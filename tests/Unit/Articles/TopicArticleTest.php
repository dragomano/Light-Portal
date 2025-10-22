<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\TopicArticle;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function() {
    Config::$modSettings['lp_frontpage_boards'] = '1';

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
        INSERT INTO board_permissions_view (id_group, id_board, deny) VALUES
        (-1, 1, 0),
        (0, 1, 0),
        (2, 1, 0)
    ")->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);
    $this->article = new TopicArticle($this->sql);
});

it('can initialize with real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';
    $this->article->init();

    expect($this->article)->toBeInstanceOf(TopicArticle::class);
});

it('can get all topics data with real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';

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

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(2)
        ->and($data)->toHaveKey(1)
        ->and($data)->toHaveKey(2)
        ->and($data[1]['title'])->toBe('Test Topic 1')
        ->and($data[2]['title'])->toBe('Test Topic 2')
        ->and($data[1]['views']['num'])->toBe(10)
        ->and($data[2]['views']['num'])->toBe(20);
});

it('can get total count with real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';

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

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(3);
});

it('can get sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title')
        ->and($options)->toHaveKey('num_views;desc');
});

it('can handle topics with images in real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';
    Config::$modSettings['lp_show_images_in_articles'] = 1;

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
        VALUES (1, 1, ?, 1, 'Topic with Image', 'Test Author', 'test@example.com', '127.0.0.1', 'Content with [img]https://example.com/image.jpg[/img]', 1)
    ", [$now]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO attachments (id_msg, id_member, attachment_type, filename, width, height, approved)
        VALUES (1, 1, 0, 'test.jpg', 100, 100, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 1, 1, 1, 0, 5, 1)
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[1]['section']['name'])->toBe('Test Board')
        ->and($data[1]['title'])->toBe('Topic with Image')
        ->and($data[1]['image'])->toBeTruthy();
});

it('can handle topics with replies in real database', function () {
    Config::$modSettings['lp_frontpage_boards'] = '1';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, name, description)
        VALUES (1, 'Test Board', 'Test board description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES
            (1, 'Topic Author', 'topic_author', 1),
            (2, 'Reply Author', 'reply_author', 0)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (1, 1, ?, 1, 'Topic with Replies', 'Topic Author', 'topic@example.com', '127.0.0.1', 'Original content', 1)
    ", [$now]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (1, 1, ?, 2, 'Re: Topic with Replies', 'Reply Author', 'reply@example.com', '127.0.0.1', 'Reply content', 1)
    ", [$now + 100]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 1, 2, 1, 1, 15, 1)
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'last_comment;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[1]['replies']['num'])->toBe(1)
        ->and($data[1]['author']['name'])->toBe('Reply Author');
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

it('returns section data', function () {
    $row = [
        'name'     => 'Test Board',
        'id_board' => 1,
    ];

    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('getSectionData', [$row]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('name')
        ->and($result)->toHaveKey('link')
        ->and($result['name'])->toBe('Test Board');
});

it('returns author data for topic author', function () {
    $row = [
        'id_member'        => 123,
        'poster_name'      => 'Test Author',
        'last_poster_id'   => 456,
        'last_poster_name' => 'Last Poster',
        'num_replies'      => 0,
    ];

    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'created;desc');
    $result = $accessor->callProtectedMethod('getAuthorData', [$row]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('id')
        ->and($result)->toHaveKey('link')
        ->and($result)->toHaveKey('name')
        ->and($result['id'])->toBe(123)
        ->and($result['name'])->toBe('Test Author');
});

it('returns author data for last poster when sorting by last_comment', function () {
    $row = [
        'id_member'        => 123,
        'poster_name'      => 'Topic Author',
        'last_poster_id'   => 456,
        'last_poster_name' => 'Last Poster',
        'num_replies'      => 5,
    ];

    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'last_comment;desc');
    $result = $accessor->callProtectedMethod('getAuthorData', [$row]);

    expect($result['id'])->toBe(456)
        ->and($result['name'])->toBe('Last Poster');
});

it('returns date based on sorting type', function () {
    $row = [
        'poster_time'   => 1000,
        'date'          => 2000,
        'last_msg_time' => 3000,
    ];

    $accessor = new ReflectionAccessor($this->article);

    $accessor->setProtectedProperty('sorting', 'created;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(1000);

    $accessor->setProtectedProperty('sorting', 'last_comment;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(3000);

    $accessor->setProtectedProperty('sorting', 'updated;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(2000);
});

it('returns views data', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getViewsData', [['num_views' => 42]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(42);
});

it('returns replies data structure', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getRepliesData', [['num_replies' => 7]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(7);
});

it('checks if topic is new', function () {
    User::$me = new User(1);
    User::$me->last_login = 500;

    $row = [
        'poster_time'     => 1000,
        'id_member'       => 2,
        'new_from'        => 1,
        'id_msg_modified' => 2,
        'last_poster_id'  => 2,
    ];

    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeTrue();

    $row['id_member'] = 1;
    $row['last_poster_id'] = 1;
    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeFalse();

    $row['new_from'] = 2000;
    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeFalse();
});

it('gets image from content and attachments', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $row = ['body' => 'Some content with [img]https://example.com/image.jpg[/img]', 'id_topic' => 1];

    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBeString();

    // Test with attachment
    $row = ['body' => 'Some content', 'id_attach' => 1, 'id_topic' => 1];
    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBeTruthy();

    Config::$modSettings['lp_show_images_in_articles'] = 0;
    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBe('');
});

it('checks topic is new correctly', function () {
    User::$me = new User(1);
    User::$me->last_login = 500;

    $row = [
        'poster_time'     => 1000,
        'id_member'       => 2,
        'new_from'        => 1,
        'id_msg_modified' => 2,
        'last_poster_id'  => 2,
    ];

    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeTrue();

    // Test with own post
    $row['id_member'] = 1;
    $row['last_poster_id'] = 1;
    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeFalse();

    // Test with old post
    $row['new_from'] = 2000;
    $row['last_poster_id'] = 2;
    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeFalse();
});

it('checks edit permissions structure', function () {
    User::$me = new User(1);
    User::$me->is_admin = true;

    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('canEdit', [['id_member' => 2]]);

    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('getEditLink', [['id_first_msg' => 42, 'id_topic' => 1]]);

    expect($result)->toBeString()
        ->and($result)->toContain('action=post;msg=42;topic=1.0');
});

it('prepares teaser with last comment content', function () {
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'last_comment;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $topic = [];
    $row = [
        'last_body'       => 'Last comment content',
        'body'            => 'Original content',
        'num_replies'     => 5,
        'smileys_enabled' => 1,
        'id_first_msg'    => 1,
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$topic, $row]);

    expect($topic)->toHaveKey('teaser');
});

it('prepares teaser with original content when no last comment', function () {
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'created;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $topic = [];
    $row = [
        'body'            => 'Original content',
        'num_replies'     => 0,
        'smileys_enabled' => 1,
        'id_first_msg'    => 1,
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$topic, $row]);

    expect($topic)->toHaveKey('teaser');
});
