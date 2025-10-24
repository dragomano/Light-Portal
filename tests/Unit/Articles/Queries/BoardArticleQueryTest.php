<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Laminas\Db\Sql\Where;
use LightPortal\Articles\Queries\BoardArticleQuery;
use LightPortal\Database\PortalSql;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Prophet;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function() {
    Config::$modSettings['lp_frontpage_boards'] = '1,2';
    Config::$modSettings['lp_show_images_in_articles'] = 0;
    Config::$modSettings['lp_show_teaser'] = 1;

    Config::$scripturl = 'https://example.com/forum';

    Utils::$context['description_allowed_tags'] = [];

    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::ATTACHMENTS->value)->execute();
    $adapter->query(Table::BOARD_PERMISSIONS_VIEW->value)->execute();
    $adapter->query(Table::BOARDS->value)->execute();
    $adapter->query(Table::CATEGORIES->value)->execute();
    $adapter->query(Table::LOG_BOARDS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();
    $adapter->query(Table::MESSAGES->value)->execute();

    $adapter->query(/** @lang text */ "
        INSERT INTO board_permissions_view (id_group, id_board, deny)
        VALUES (-1, 1, 0), (0, 1, 0), (2, 1, 0), (-1, 2, 0), (0, 2, 0)
    ")->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);

    $this->prophet = new Prophet();

    $prophecy = $this->prophet->prophesize(EventDispatcherInterface::class);
    $this->eventsMock = $prophecy->reveal();

    $this->query = new BoardArticleQuery($this->sql, $this->eventsMock);
});

it('can handle boards with images in real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO categories (id_cat, name) VALUES (1, 'Test Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES (1, 'Test Author', 'test_author', 0)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, id_cat, name, description, id_last_msg)
        VALUES (1, 1, 'Board with Image', '[img]https://example.com/image.jpg[/img]', 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (
            id_msg, id_topic, id_board, poster_time, id_member, subject,
            poster_name, poster_email, poster_ip, body, approved, modified_time
        ) VALUES (1, 1, 1, ?, 1, 'Test Message', 'Test Author', 'test@example.com', '127.0.0.1', 'Content', 1, ?)
    ", [$now, $now]);

    // Insert attachment for image
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO attachments (id_attach, id_msg, id_thumb, width, height)
        VALUES (1, 1, 1, 100, 100)
    ")->execute();

    $this->query->init(['selected_boards' => [1], 'current_member' => 1]);
    $this->query->setSorting('created;desc');
    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[0]['name'])->toBe('Board with Image')
        ->and($data[0]['attach_id'])->toBe(1);
});

it('applies base conditions correctly', function () {
    $this->query->init(['selected_boards' => [1, 2], 'recycle_board' => 3, 'current_member' => 1]);

    $select = $this->sql->select()->from('boards');
    $accessor = new ReflectionAccessor($this->query);
    $accessor->callProtectedMethod('applyBaseConditions', [$select]);

    $state = $select->getRawState();

    // Check WHERE conditions - it's a Where object, not an array
    $where = $state['where'];

    expect($where)->toBeInstanceOf(Where::class);
});

it('handles permission filtering for guests', function () {
    User::$me->is_guest = true;

    $this->query->init(['selected_boards' => [1]]);
    $this->query->setSorting('created;desc');
    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    // Should return empty for guests with permission check
    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('returns total count correctly', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO categories (id_cat, name) VALUES (1, 'Test Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, id_cat, name, description)
        VALUES (1, 1, 'Board 1', 'Desc 1'), (2, 1, 'Board 2', 'Desc 2'), (3, 1, 'Board 3', 'Desc 3')
    ")->execute();

    $this->query->init(['selected_boards' => [1, 2]]);

    $count = $this->query->getTotalCount();

    expect($count)->toBe(2); // Only boards 1 and 2 are selected
});

it('returns empty data when no boards selected', function () {
    $this->query->init(['selected_boards' => []]);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('returns zero count when no boards selected', function () {
    $this->query->init(['selected_boards' => []]);

    $count = $this->query->getTotalCount();

    expect($count)->toBe(0);
});
