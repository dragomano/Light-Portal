<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalTransactionInterface;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\DataManagerInterface;
use LightPortal\Repositories\PageRepository;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Utils\NotifierInterface;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;
use Tests\Unit\Repositories\PageRepositoryTestProvider;

beforeEach(function() {
    Lang::$txt['today'] = 'Today at';
    Lang::$txt['yesterday'] = 'Yesterday at';

    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->groups = [0];

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::CATEGORIES->value)->execute();
    $adapter->query(PortalTable::COMMENTS->value)->execute();
    $adapter->query(PortalTable::PAGE_TAG->value)->execute();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();
    $adapter->query(PortalTable::TAGS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);

    $transactionMock = mock(PortalTransactionInterface::class);
    $transactionMock->shouldReceive('begin')->andReturnUsing(function () use ($adapter) {
        $adapter->getDriver()->getConnection()->beginTransaction();
    });
    $transactionMock->shouldReceive('commit')->andReturnUsing(function () use ($adapter) {
        $adapter->getDriver()->getConnection()->commit();
    });
    $transactionMock->shouldReceive('rollback')->andReturnUsing(function () use ($adapter) {
        $adapter->getDriver()->getConnection()->rollback();
    });

    $this->transaction = $transactionMock;

    $dispatcherMock = mock(EventDispatcherInterface::class);
    $dispatcherMock->shouldReceive('dispatch')->andReturnNull()->byDefault();
    $this->dispatcher = $dispatcherMock;

    $notifierMock = mock(NotifierInterface::class);
    $this->notifier = $notifierMock;

    $this->repository = new PageRepository($this->sql, $this->dispatcher, $this->notifier);

    $reflection = new ReflectionAccessor($this->repository);
    $reflection->setProperty('transaction', $this->transaction);
});

arch()
    ->expect(PageRepository::class)
    ->toExtend(AbstractRepository::class)
    ->toImplement(PageRepositoryInterface::class)
    ->toImplement(DataManagerInterface::class);

it('can get all pages with default parameters', function () {
    // Insert test data
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (real_name, member_name)
        VALUES ('Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Test Page', 'Test content', 'Test description')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'created_at DESC');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey(1)
        ->and($result[1]['id'])->toBe(1)
        ->and($result[1]['slug'])->toBe('test-page')
        ->and($result[1]['title'])->toBe('Test Page')
        ->and($result[1]['author_name'])->toBe('Test Author');
});

it('can get all pages with empty result', function () {
    // Clear tables before test
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_pages')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_translations')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_params')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_comments')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_categories')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM members')->execute();

    $result = $this->repository->getAll(0, 10, 'created_at DESC');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('can get all pages with list filter', function () {
    // Insert test data
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (real_name, member_name)
        VALUES ('Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Test Page', 'Test content', 'Test description')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'created_at DESC', 'list');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey(1)
        ->and($result[1]['status'])->toBe(Status::ACTIVE->value);
});

it('can get total count', function () {
    $now = time();

    // Insert test data (multiple rows)
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, ?, ?, ?, ?, 10, 5, ?, ?, 0, 0, 0),
            (0, 1, ?, ?, ?, ?, 10, 5, ?, ?, 0, 0, 0)
    ", [
        'test-page1', ContentType::BBC->name(), EntryType::DEFAULT->name(), Permission::ALL->value, $now, $now,
        'test-page2', ContentType::BBC->name(), EntryType::DEFAULT->name(), Permission::ALL->value, $now, $now,
    ]);

    $result = $this->repository->getTotalCount();

    expect($result)->toBe(2);
});

it('can get data by id', function () {
    // Insert test data
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 5, ?, 0, 0, 1)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (real_name, member_name)
        VALUES ('Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Test Page', '[b]Test content[/b]', 'Test description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_params (item_id, type, name, value)
        VALUES (1, 'page', 'allow_comments', '1')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (parent_id, page_id, author_id, created_at, updated_at)
        VALUES (0, 1, 1, ?, 0)
    ", [time()]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES (1, 'comment', 'english', 'Test comment')
    ")->execute();

    $result = $this->repository->getData(1);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('id')
        ->and($result['id'])->toBe(1)
        ->and($result['slug'])->toBe('test-page')
        ->and($result['title'])->toBe('Test Page')
        ->and($result)->toHaveKey('options')
        ->and($result['options']['allow_comments'])->toBe('1')
        ->and($result['last_comment_id'])->toBe(1);
});

it('can get data by slug', function () {
    // Insert test data
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (real_name, member_name)
        VALUES ('Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Test Page', '<p>Test content</p>', 'Test description')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_params (item_id, type, name, value)
        VALUES (1, 'page', 'allow_comments', '1')
    ")->execute();

    $result = $this->repository->getData('test-page');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('id')
        ->and($result['id'])->toBe(1)
        ->and($result['slug'])->toBe('test-page')
        ->and($result['content'])->toBe('<p>Test content</p>')
        ->and($result)->toHaveKey('options')
        ->and($result['options']['allow_comments'])->toBe('1');
});

it('returns empty array when item is empty', function () {
    $result = $this->repository->getData('');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns empty array when translations are missing', function () {
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_pages')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_translations')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_params')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_comments')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM lp_categories')->execute();
    $this->sql->getAdapter()->query(/** @lang text */ 'DELETE FROM members')->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $result = $this->repository->getData(1);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('title')
        ->and($result['title'])->toBe('');
});

$provider = new PageRepositoryTestProvider();
[$dataset, $mockData, $cmpMap] = $provider->generatePrevNextTestData();

it('can get prev next links', function (
    $sorting,
    $pageId,
    $expectedPrevTitle,
    $expectedPrevSlug,
    $expectedPrevId,
    $expectedNextTitle,
    $expectedNextSlug,
    $expectedNextId,
    $withinCategory,
    $pagesForSorting
) use ($mockData, $cmpMap) {
    $pageData = null;

    foreach ($mockData as $item) {
        if ($item['id'] === $pageId) {
            $pageData = [
                'id'              => $item['id'],
                'category_id'     => $item['category_id'],
                'author'          => $item['author'],
                'created_at'      => $item['created_at'],
                'updated_at'      => $item['updated_at'],
                'last_comment_id' => $item['last_comment_id'],
                'sort_value'      => $item['sort_value'],
                'num_views'       => $item['num_views'],
                'num_comments'    => $item['num_comments'],
                'entry_type'      => $item['entry_type'],
                'status'          => $item['status'],
                'title'           => $item['title'],
            ];

            break;
        }
    }

    $authorMap = [];
    foreach ($pagesForSorting as $page) {
        $authorMap[$page['author_id']] = $page['author'];
    }

    $adapter = $this->sql->getAdapter();

    $authorIds = array_unique(array_column($pagesForSorting, 'author_id'));
    if (! empty($authorIds)) {
        $memberStmt = $adapter->createStatement(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (?, ?, ?)
    ");

        foreach ($authorIds as $authorId) {
            $authorName = $authorMap[$authorId] ?? 'Unknown';
            $memberStmt->execute([
                $authorId,
                $authorName,
                'user' . $authorId
            ]);
        }
    }

    $pageStmt = $adapter->createStatement(/** @lang text */ "
        INSERT INTO lp_pages (
            page_id, category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
    ");

    $transStmt = $adapter->createStatement(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $commentStmt = $adapter->createStatement(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($pagesForSorting as $page) {
        $pageStmt->execute([
            $page['id'],
            $page['category_id'],
            $page['author_id'],
            $page['slug'],
            $page['type'],
            $page['entry_type'],
            $page['permissions'],
            $page['status'],
            $page['num_views'],
            $page['num_comments'],
            $page['created_at'],
            $page['updated_at'],
            $page['last_comment_id'],
        ]);

        $transStmt->execute([
            $page['id'],
            'page',
            'english',
            $page['title'],
            'Test content',
            'Test description',
        ]);

        if ($page['last_comment_id'] > 0) {
            $commentStmt->execute([
                $page['last_comment_id'],
                0,
                $page['id'],
                $page['author_id'],
                $page['sort_value'],
                0,
            ]);

            $this->sql->getAdapter()->query(/** @lang text */ "
                INSERT INTO lp_translations (item_id, type, lang, content)
                VALUES (?, 'comment', 'english', 'Test comment')
            ", [$page['last_comment_id']]);
        }
    }

    Utils::$context['lp_current_sorting'] = $sorting;

    Config::$modSettings['lp_frontpage_categories'] = implode(',', range(0, 10));

    $links = $this->repository->getPrevNextLinks($pageData, $withinCategory);

    expect($links[0])->toBe($expectedPrevTitle)
        ->and($links[1])->toBe($expectedPrevSlug)
        ->and($links[2])->toBe($expectedNextTitle)
        ->and($links[3])->toBe($expectedNextSlug);
})->with($dataset);
