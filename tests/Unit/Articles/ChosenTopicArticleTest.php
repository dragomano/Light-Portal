<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\ChosenTopicArticle;
use LightPortal\Articles\TopicArticle;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function() {
    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->groups = [0];

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
    $this->article = new ChosenTopicArticle($this->sql);
});

arch()
    ->expect(ChosenTopicArticle::class)
    ->toExtend(TopicArticle::class)
    ->toImplement(ArticleInterface::class);

it('can initialize with selected topics', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1,3,5';

    $this->article->init();

    $accessor = new ReflectionAccessor($this->article);
    $selectedTopics = $accessor->getProtectedProperty('selectedTopics');
    $selectedBoards = $accessor->getProtectedProperty('selectedBoards');
    $wheres = $accessor->getProtectedProperty('wheres');
    $params = $accessor->getProtectedProperty('params');

    expect($selectedTopics)->toBe(['1', '3', '5'])
        ->and($selectedBoards)->toBe([])
        ->and($wheres)->toContain(['t.id_topic' => ['1', '3', '5']])
        ->and($params)->toHaveKey('selected_topics')
        ->and($params['selected_topics'])->toBe(['1', '3', '5']);
});

it('can get selected topics data with real database', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1,2';

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

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (3, 1, ?, 1, 'Test Topic 3', 'Test Author', 'test@example.com', '127.0.0.1', 'Test content 3', 1)
    ", [$now + 300]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 4, 4, 1, 0, 30, 1)
    ")->execute();

    $this->article->init();
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('selectedBoards', ['1']); // Allow parent getData to proceed
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

it('can get total count for selected topics', function () {
    Config::$modSettings['lp_frontpage_topics'] = '1,3';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, name, description)
        VALUES (1, 'Test Board', 'Test board description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES (1, 'Test Author', 'test_author', 0)
    ")->execute();

    for ($i = 1; $i <= 4; $i++) {
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
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('selectedBoards', ['1']); // Allow parent getTotalCount to proceed
    $count = $this->article->getTotalCount();

    expect($count)->toBe(2);
});

it('can get sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title')
        ->and($options)->toHaveKey('num_views;desc');
});

it('returns empty array when no selected topics for getData', function () {
    Config::$modSettings['lp_frontpage_topics'] = '';

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns zero count when no selected topics for getTotalCount', function () {
    Config::$modSettings['lp_frontpage_topics'] = '';

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(0);
});

it('filters topics correctly based on selected topics', function () {
    Config::$modSettings['lp_frontpage_topics'] = '2,4';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO boards (id_board, name, description)
        VALUES (1, 'Test Board', 'Test board description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, id_group)
        VALUES (1, 'Test Author', 'test_author', 0)
    ")->execute();

    for ($i = 1; $i <= 4; $i++) {
        $this->sql->getAdapter()->query(/** @lang text */ "
            INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
            VALUES (?, 1, ?, 1, ?, 'Test Author', 'test@example.com', '127.0.0.1', ?, 1)
        ", [$i, $now + ($i * 100), "Topic $i", "Content $i"]);

        $this->sql->getAdapter()->query(/** @lang text */ "
            INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
            VALUES (1, ?, ?, 1, 0, $i * 10, 1)
        ", [$i, $i]);
    }

    $this->article->init();
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('selectedBoards', ['1']); // Allow parent getData to proceed
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(2)
        ->and($data)->toHaveKey(2)
        ->and($data)->toHaveKey(4)
        ->and($data[2]['title'])->toBe('Topic 2')
        ->and($data[4]['title'])->toBe('Topic 4');
});

it('handles empty selected topics setting', function () {
    Config::$modSettings['lp_frontpage_topics'] = '';

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');
    $count = $this->article->getTotalCount();

    expect($result)->toBeEmpty()
        ->and($count)->toBe(0);
});

it('handles single selected topic', function () {
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
        VALUES (1, 1, ?, 1, 'Topic 1', 'Test Author', 'test@example.com', '127.0.0.1', 'Content 1', 1)
    ", [$now]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 1, 1, 1, 0, 10, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO messages (id_topic, id_board, poster_time, id_member, subject, poster_name, poster_email, poster_ip, body, approved)
        VALUES (2, 1, ?, 1, 'Topic 2', 'Test Author', 'test@example.com', '127.0.0.1', 'Content 2', 1)
    ", [$now]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO topics (id_board, id_first_msg, id_last_msg, id_member_started, num_replies, num_views, approved)
        VALUES (1, 2, 2, 1, 0, 20, 1)
    ")->execute();

    $this->article->init();
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('selectedBoards', ['1']); // Allow parent methods to proceed
    $result = $this->article->getData(0, 10, 'created;desc');
    $count = $this->article->getTotalCount();

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data)->toHaveKey(1)
        ->and($data[1]['title'])->toBe('Topic 1')
        ->and($count)->toBe(1);
});
