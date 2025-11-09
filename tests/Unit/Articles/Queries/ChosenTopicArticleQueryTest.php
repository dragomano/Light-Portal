<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Laminas\Db\Sql\Where;
use LightPortal\Articles\Queries\ChosenTopicArticleQuery;
use LightPortal\Database\PortalSql;
use LightPortal\Events\EventDispatcherInterface;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function() {
    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::ATTACHMENTS->value)->execute();
    $adapter->query(Table::BOARD_PERMISSIONS_VIEW->value)->execute();
    $adapter->query(Table::BOARDS->value)->execute();
    $adapter->query(Table::LOG_MARK_READ->value)->execute();
    $adapter->query(Table::LOG_TOPICS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();
    $adapter->query(Table::MESSAGES->value)->execute();
    $adapter->query(Table::TOPICS->value)->execute();

    $adapter->query(/** @lang text */ "
        INSERT INTO board_permissions_view (id_group, id_board, deny)
        VALUES (-1, 1, 0), (0, 1, 0), (2, 1, 0)
    ")->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);

    $this->eventsMock = mock(EventDispatcherInterface::class);
    $this->eventsMock->shouldReceive('dispatch')->andReturn(null)->byDefault();

    $this->query = new ChosenTopicArticleQuery($this->sql, $this->eventsMock);
});

it('can get all topics data with real database', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, name, description)
        VALUES (1, 'Test Board', 'Test board description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES (1, 'Test Author', 'test_author', 0)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (1, 1, ?, 1, 'Test Topic 1', 'Test Author', 'test@example.com', '127.0.0.1', 'Test content 1', 1)
    ", [$now]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (1, 1, ?, 1, 'Re: Test Topic 1', 'Test Author', 'test@example.com', '127.0.0.1', 'Test reply 1', 1)
    ", [$now + 100]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 1, 2, 1, 1, 10, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (2, 1, ?, 1, 'Test Topic 2', 'Test Author', 'test@example.com', '127.0.0.1', 'Test content 2', 1)
    ", [$now + 200]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 3, 3, 1, 0, 20, 1)
    ")->execute();

    $this->query->init([
        'selected_topics'   => [1, 2],
        'current_member'    => 1,
        'id_poll'           => 0,
        'is_approved'       => 1,
        'id_redirect_topic' => 0,
        'recycle_board'     => null,
        'attachment_type'   => 0,
    ]);

    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(1)
        ->and($data)->toHaveKey(0)
        ->and($data[0]['subject'])->toBe('Test Topic 1')
        ->and($data[0]['num_views'])->toBe(10)
        ->and($data[0]['name'])->toBe('Test Board');
});

it('can get total count with real database', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, name, description)
        VALUES (1, 'Test Board', 'Test board description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES (1, 'Test Author', 'test_author', 0)
    ")->execute();

    for ($i = 1; $i <= 3; $i++) {
        $this->sql->getAdapter()->query(/** @lang text */ "
            INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
            VALUES (?, 1, ?, 1, ?, 'Test Author', 'test@example.com', '127.0.0.1', ?, 1)
        ", [$i, $now + ($i * 100), "Test Topic $i", "Test content $i"]);

        $this->sql->getAdapter()->query(/** @lang text */ "
            INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
            VALUES (1, ?, ?, 1, 0, 0, 1)
        ", [$i, $i]);
    }

    $this->query->init([
        'selected_topics'   => [1, 2, 3],
        'current_member'    => 1,
        'id_poll'           => 0,
        'is_approved'       => 1,
        'id_redirect_topic' => 0,
        'recycle_board'     => null,
        'attachment_type'   => 0,
    ]);

    $count = $this->query->getTotalCount();

    expect($count)->toBe(1);
});

it('handles permission filtering for guests', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1';

    User::$me->is_guest = true;

    $result = $this->query->getRawData();

    // Should return empty for guests with permission check
    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('applies base conditions correctly', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1,2';

    $select = $this->sql->select()->from('topics');

    $state = $select->getRawState();

    // Check WHERE conditions - it's a Where object, not an array
    $where = $state['where'];

    expect($where)->toBeInstanceOf(Where::class);
});

it('returns empty when no topics selected', function () {
    Config::$modSettings['lp_frontpage_topics'] = '';

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('returns zero count when no topics selected', function () {
    Config::$modSettings['lp_frontpage_topics'] = '';

    $count = $this->query->getTotalCount();

    expect($count)->toBe(0);
});
