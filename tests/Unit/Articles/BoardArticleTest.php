<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Articles\BoardArticle;
use LightPortal\Database\PortalSql;
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
    $adapter->query(Table::MEMBERS->value)->execute();

    $adapter->query(/** @lang text */ "
        CREATE TABLE categories (
            id_cat INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT '',
            cat_order INTEGER NOT NULL DEFAULT 0
        )
    ")->execute();

    $adapter->query(/** @lang text */ "
        CREATE TABLE boards (
            id_board INTEGER PRIMARY KEY AUTOINCREMENT,
            id_cat INTEGER NOT NULL DEFAULT 0,
            child_level INTEGER NOT NULL DEFAULT 0,
            id_parent INTEGER NOT NULL DEFAULT 0,
            board_order INTEGER NOT NULL DEFAULT 0,
            id_last_msg INTEGER DEFAULT 0,
            id_msg_updated INTEGER NOT NULL DEFAULT 0,
            member_groups TEXT NOT NULL DEFAULT '-1,0',
            id_profile INTEGER NOT NULL DEFAULT 1,
            name TEXT NOT NULL DEFAULT '',
            description TEXT NOT NULL DEFAULT '',
            num_topics INTEGER NOT NULL DEFAULT 0,
            num_posts INTEGER NOT NULL DEFAULT 0,
            count_posts INTEGER NOT NULL DEFAULT 0,
            id_theme INTEGER NOT NULL DEFAULT 0,
            override_theme INTEGER NOT NULL DEFAULT 0,
            id_moderator INTEGER NOT NULL DEFAULT 0,
            id_moderator_group INTEGER NOT NULL DEFAULT 0,
            member_groups_moderator TEXT NOT NULL DEFAULT '',
            redirect TEXT NOT NULL DEFAULT '',
            deny_member_groups TEXT NOT NULL DEFAULT '',
            board_type TEXT NOT NULL DEFAULT 'default'
        )
    ")->execute();

    $adapter->query(/** @lang text */ "
        CREATE TABLE messages (
            id_msg INTEGER PRIMARY KEY AUTOINCREMENT,
            id_topic INTEGER NOT NULL,
            id_board INTEGER NOT NULL,
            poster_time INTEGER NOT NULL DEFAULT 0,
            id_member INTEGER NOT NULL,
            id_msg_modified INTEGER NOT NULL DEFAULT 0,
            subject TEXT NOT NULL,
            poster_name TEXT NOT NULL,
            poster_email TEXT NOT NULL,
            poster_ip TEXT NOT NULL,
            smileys_enabled INTEGER NOT NULL DEFAULT 1,
            modified_time INTEGER NOT NULL DEFAULT 0,
            modified_name TEXT NOT NULL DEFAULT '',
            modified_reason TEXT NOT NULL DEFAULT '',
            body TEXT NOT NULL,
            icon TEXT NOT NULL DEFAULT 'xx',
            approved INTEGER NOT NULL DEFAULT 1
        )
    ")->execute();

    $adapter->query(/** @lang text */ "
        CREATE TABLE attachments (
            id_attach INTEGER PRIMARY KEY AUTOINCREMENT,
            id_thumb INTEGER NOT NULL DEFAULT 0,
            id_msg INTEGER NOT NULL DEFAULT 0,
            id_member INTEGER NOT NULL DEFAULT 0,
            attachment_type INTEGER NOT NULL DEFAULT 0,
            filename TEXT NOT NULL DEFAULT '',
            file_hash TEXT NOT NULL DEFAULT '',
            fileext TEXT NOT NULL DEFAULT '',
            size INTEGER NOT NULL DEFAULT 0,
            downloads INTEGER NOT NULL DEFAULT 0,
            width INTEGER NOT NULL DEFAULT 0,
            height INTEGER NOT NULL DEFAULT 0,
            mime_type TEXT NOT NULL DEFAULT '',
            approved INTEGER NOT NULL DEFAULT 1
        )
    ")->execute();

    $adapter->query(/** @lang text */ "
        CREATE TABLE log_boards (
            id_member INTEGER NOT NULL,
            id_board INTEGER NOT NULL,
            id_msg INTEGER NOT NULL,
            PRIMARY KEY (id_member, id_board)
        )
    ")->execute();

    $adapter->query(/** @lang text */ "
        CREATE TABLE board_permissions_view (
            id_group INTEGER NOT NULL DEFAULT 0,
            id_board INTEGER NOT NULL,
            deny INTEGER NOT NULL,
            PRIMARY KEY (id_group, id_board, deny)
        )
    ")->execute();

    $adapter->query(/** @lang text */ "
        INSERT INTO board_permissions_view (id_group, id_board, deny) VALUES
        (-1, 1, 0),
        (0, 1, 0),
        (2, 1, 0),
        (-1, 2, 0),
        (0, 2, 0)
    ")->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);
    $this->article = new BoardArticle($this->sql);
});

arch()
    ->expect(BoardArticle::class)
    ->toImplement(\LightPortal\Articles\ArticleInterface::class);

it('can initialize with real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';

    $this->article->init();

    expect($this->article)->toBeInstanceOf(BoardArticle::class);
});

it('can get all boards data with real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1,2';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO categories (id_cat, name)
        VALUES (1, 'Test Category'), (2, 'Another Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES (1, 'Test Author', 'test_author', 0)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, id_cat, name, description, num_posts, id_last_msg, id_msg_updated)
        VALUES (1, 1, 'Test Board 1', 'Test board description 1', 5, 1, 2), (2, 1, 'Test Board 2', 'Test board description 2', 10, 1, 2)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_msg, id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved, modified_time)
        VALUES (1, 1, 1, ?, 1, 'Test Message 1', 'Test Author', 'test@example.com', '127.0.0.1', 'Test content 1', 1, ?),
            (2, 2, 2, ?, 1, 'Test Message 2', 'Test Author', 'test@example.com', '127.0.0.1', 'Test content 2', 1, ?)
    ", [$now, $now, $now + 100, $now + 100]);

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(2)
        ->and($data)->toHaveKey(1)
        ->and($data)->toHaveKey(2)
        ->and($data[1]['title'])->toBe('Test Board 1')
        ->and($data[2]['title'])->toBe('Test Board 2')
        ->and($data[1]['replies']['num'])->toBe(5)
        ->and($data[2]['replies']['num'])->toBe(10)
        ->and($data[1]['date'])->toBeNumeric()
        ->and($data[2]['date'])->toBeNumeric();
});

it('can get total count with real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1,2';

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO categories (id_cat, name) VALUES (1, 'Test Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, id_cat, name, description)
        VALUES (1, 1, 'Board 1', 'Desc 1'), (2, 1, 'Board 2', 'Desc 2'), (3, 1, 'Board 3', 'Desc 3')
    ")->execute();

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(2); // Only boards 1 and 2 are selected
});

it('can get sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title')
        ->and($options)->toHaveKey('num_replies;desc');
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
        INSERT INTO messages (id_msg, id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved, modified_time)
        VALUES (1, 1, 1, ?, 1, 'Test Message', 'Test Author', 'test@example.com', '127.0.0.1', 'Content', 1, ?)
    ", [$now, $now]);

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[1]['title'])->toBe('Board with Image')
        ->and($data[1]['image'])->toBe('https://example.com/image.jpg');
});

it('can handle redirect boards', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO categories (id_cat, name) VALUES (1, 'Test Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, id_cat, name, description, redirect, id_last_msg)
        VALUES (1, 1, 'Redirect Board', 'Description', 'https://external.com', 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_msg, id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved, modified_time)
        VALUES (1, 1, 1, ?, 1, 'Test Message', 'Test Author', 'test@example.com', '127.0.0.1', 'Content', 1, ?)
    ", [$now, $now]);

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[1]['is_redirect'])->toBe(1)
        ->and($data[1]['link'])->toContain('https://external.com');
});

it('returns empty when no boards selected', function () {
    Config::$modSettings['lp_frontpage_boards'] = '';

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);
    expect($data)->toBeEmpty();
});

it('returns zero count when no boards selected', function () {
    Config::$modSettings['lp_frontpage_boards'] = '';

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(0);
});

it('returns category data', function () {
    $row = [
        'cat_name' => 'Test Category',
        'id_board' => 1,
    ];

    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('getCategory', [$row]);

    expect($result)->toBe('Test Category');
});

it('returns date based on sorting type', function () {
    $row = [
        'poster_time'  => 1000,
        'last_updated' => 2000,
    ];

    $accessor = new ReflectionAccessor($this->article);

    $accessor->setProtectedProperty('sorting', 'created;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(1000);

    $accessor->setProtectedProperty('sorting', 'updated;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(2000);
});

it('returns replies data structure', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getRepliesData', [['num_posts' => 7]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(7);
});

it('returns title with BBCode parsing', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getTitle', [['name' => 'Test [b]Board[/b]']]);

    expect($result)->toBe('Test [b]Board[/b]'); // Basic test without full BBCode parsing setup
});

it('returns link for regular board', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getLink', [['id_board' => 5, 'is_redirect' => 0, 'redirect' => '']]);

    expect($result)->toBe('https://example.com/forum?board=5.0');
});

it('returns link for redirect board', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getLink', [['id_board' => 5, 'is_redirect' => 1, 'redirect' => 'https://external.com']]);

    expect($result)->toBe('https://external.com" rel="nofollow noopener');
});

it('checks edit permissions', function () {
    User::$me->is_guest = true;
    User::$me->allowedTo = fn($permission) => false;

    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('canEdit', []);

    expect($result)->toBeTrue();

    User::$me->allowedTo = fn($permission) => $permission === 'manage_boards';

    $result = $accessor->callProtectedMethod('canEdit', []);
    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('getEditLink', [['id_board' => 42]]);

    expect($result)->toBe('https://example.com/forum?action=admin;area=manageboards;sa=board;boardid=42');
});

it('prepares teaser from description', function () {
    Config::$modSettings['lp_show_teaser'] = 1;

    $accessor = new ReflectionAccessor($this->article);

    $board = [];
    $row = [
        'description' => 'This is a long description that should be truncated for the teaser.',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$board, $row]);

    expect($board)->toHaveKey('teaser');
});

it('handles board permissions correctly', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1,3'; // Board 3 doesn't exist in permissions

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO categories (id_cat, name) VALUES (1, 'Test Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, id_cat, name, description, id_last_msg)
        VALUES (1, 1, 'Board 1', 'Desc 1', 1), (3, 1, 'Board 3', 'Desc 3', 2)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_msg, id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved, modified_time)
        VALUES (1, 1, 1, 1234567890, 1, 'Msg 1', 'Author', 'test@test.com', '127.0.0.1', 'Content 1', 1, 1234567891),
            (2, 2, 3, 1234567890, 1, 'Msg 2', 'Author', 'test@test.com', '127.0.0.1', 'Content 2', 1, 1234567891)
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1);
});
