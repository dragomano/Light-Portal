<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSql;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Repositories\AbstractRepository;
use Bugo\LightPortal\Repositories\DataManagerInterface;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Repositories\PageRepositoryInterface;
use Bugo\LightPortal\Utils\Notifier;
use Tests\Table;
use Tests\TestAdapterFactory;

use function Pest\Faker\fake;

arch()
    ->expect(PageRepository::class)
    ->toExtend(AbstractRepository::class)
    ->toImplement(PageRepositoryInterface::class)
    ->toImplement(DataManagerInterface::class);

beforeEach(function() {
    Lang::$txt['guest_title'] = 'Guest';
    Lang::$txt['today'] = 'Today at';
    Lang::$txt['yesterday'] = 'Yesterday at';
    Lang::$txt['lp_just_now'] = 'just now';
    Lang::$txt['lp_tomorrow'] = 'Tomorrow at';
    Lang::$txt['lp_time_label_in'] = 'in %s';
    Lang::$txt['lp_time_label_ago'] = ' ago';

    User::$me->language = 'english';

    Utils::$smcFunc['strtolower'] = fn($string) => strtolower($string);

    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::PAGES->value)->execute();
    $adapter->query(Table::COMMENTS->value)->execute();
    $adapter->query(Table::PARAMS->value)->execute();
    $adapter->query(Table::TRANSLATIONS->value)->execute();
    $adapter->query(Table::CATEGORIES->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();
    $adapter->query(Table::TAGS->value)->execute();
    $adapter->query(Table::PAGE_TAG->value)->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);
    $this->notifier = mock(Notifier::class);
    $this->repository = new PageRepository($this->sql, $this->notifier);
});

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
        INSERT INTO lp_comments (parent_id, page_id, author_id, message, created_at)
        VALUES (0, 1, 1, 'Test comment', " . time() . ")
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

$commentCounter = 1001;
$mockData = [];
for ($i = 0; $i < 36; $i++) {
    $createdAt = fake()->dateTimeBetween('-2 years')->getTimestamp();
    $updatedAt = fake()->boolean(20) ? 0 : fake()->dateTimeBetween('@' . $createdAt)->getTimestamp();
    $hasComments = fake()->boolean(70); // 70% chance of having comments
    $mockData[] = [
        'id'              => $i + 1,
        'category_id'     => fake()->numberBetween(0, 10),
        'author_id'       => fake()->numberBetween(1, 100),
        'author'          => fake()->name(),
        'slug'            => fake()->slug(),
        'type'            => fake()->randomElement(['bbc', 'html', 'php']),
        'entry_type'      => EntryType::DEFAULT->name(),
        'permissions'     => fake()->numberBetween(Permission::ADMIN->value, Permission::ALL->value),
        'status'          => Status::ACTIVE->value,
        'num_views'       => fake()->numberBetween(1, 10000),
        'num_comments'    => $hasComments ? fake()->numberBetween(1, 100) : 0,
        'created_at'      => $createdAt,
        'updated_at'      => $updatedAt,
        'last_comment_id' => $hasComments ? $commentCounter++ : 0,
        'sort_value'      => $hasComments ? fake()->dateTimeBetween('@' . $createdAt)->getTimestamp() : $createdAt,
        'image'           => fake()->imageUrl(),
        'title'           => fake()->sentence(),
        'content'         => fake()->paragraphs(2, true),
        'description'     => fake()->sentence(),
        'options'         => [],
    ];
}

$allowedCategories = range(0, 10);

$cmpMap = [
    'created;desc' => fn($a, $b) => $b['created_at'] <=> $a['created_at']
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'created' => fn($a, $b) => $a['created_at'] <=> $b['created_at']
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],

    'updated;desc' => fn($a, $b) => max($b['created_at'], $b['updated_at']) <=> max($a['created_at'], $a['updated_at'])
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'updated' => fn($a, $b) => max($a['created_at'], $a['updated_at']) <=> max($b['created_at'], $b['updated_at'])
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],

    'last_comment;desc' => fn($a, $b) => $b['sort_value'] <=> $a['sort_value']
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'last_comment' => fn($a, $b) => $a['sort_value'] <=> $b['sort_value']
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],

    'title;desc' => fn($a, $b) => strtolower($b['title']) <=> strtolower($a['title'])
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'title' => fn($a, $b) => strtolower($a['title']) <=> strtolower($b['title'])
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],

    'author_name;desc' => fn($a, $b) => strtolower($b['author']) <=> strtolower($a['author'])
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'author_name' => fn($a, $b) => strtolower($a['author']) <=> strtolower($b['author'])
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],

    'num_views;desc' => fn($a, $b) => $b['num_views'] <=> $a['num_views']
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'num_views' => fn($a, $b) => $a['num_views'] <=> $b['num_views']
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],

    'num_replies;desc' => fn($a, $b) => $b['num_comments'] <=> $a['num_comments']
        ?: $b['created_at'] <=> $a['created_at']
        ?: $b['id'] <=> $a['id'],

    'num_replies' => fn($a, $b) => $a['num_comments'] <=> $b['num_comments']
        ?: $a['created_at'] <=> $b['created_at']
        ?: $a['id'] <=> $b['id'],
];

dataset('prev and next links', function() use ($mockData, $cmpMap, $allowedCategories) {
    $filteredAll = array_filter($mockData, function($item) use ($allowedCategories) {
        if ($item['created_at'] > time()) {
            return false;
        }

        if ($item['entry_type'] !== EntryType::DEFAULT->name()) {
            return false;
        }

        if ($item['status'] !== Status::ACTIVE->value) {
            return false;
        }

        if ($item['permissions'] !== Permission::ALL->value) {
            return false;
        }

        if (! in_array($item['category_id'], $allowedCategories)) {
            return false;
        }

        return true;
    });

    $filteredAll = array_values($filteredAll);
    $selectedPages = array_slice($filteredAll, 0, 10);
    $selectedPage = $selectedPages[array_rand($selectedPages)];

    $sortingTypes = [
        'created;desc', 'created',
        'updated;desc', 'updated',
        'last_comment;desc', 'last_comment',
        'title;desc', 'title',
        'author_name;desc', 'author_name',
        'num_views;desc', 'num_views',
        'num_replies;desc', 'num_replies',
    ];

    $dataset = [];
    foreach ($sortingTypes as $sorting) {
        foreach ([true, false] as $withinCategory) {
            $cmp = $cmpMap[$sorting];

            $pagesForSorting = $selectedPages;
            if ($withinCategory) {
                $pagesForSorting = array_filter(
                    $pagesForSorting,
                    fn($p) => $p['category_id'] == $selectedPage['category_id']
                );
            }
            usort($pagesForSorting, $cmp);

            $compare = function($a, $b, $op) {
                return match($op) {
                    '>' => $a > $b,
                    '<' => $a < $b,
                    '==' => $a == $b,
                    default => false,
                };
            };

            $listAsc = !str_contains($sorting, ';desc');
            $nextPrimaryOp = $listAsc ? '>' : '<';
            $nextSecondaryOp = $listAsc ? '>' : '<';
            $prevPrimaryOp = $listAsc ? '<' : '>';
            $prevSecondaryOp = $listAsc ? '<' : '>';

            $currentPrimary = match (true) {
                str_contains($sorting, 'updated')      => max($selectedPage['created_at'], $selectedPage['updated_at']),
                str_contains($sorting, 'last_comment') => $selectedPage['sort_value'] ?? $selectedPage['created_at'],
                str_contains($sorting, 'title')        => strtolower($selectedPage['title']),
                str_contains($sorting, 'author_name')  => strtolower($selectedPage['author']),
                str_contains($sorting, 'num_views')    => $selectedPage['num_views'],
                str_contains($sorting, 'num_replies')  => $selectedPage['num_comments'],
                default => $selectedPage['created_at'],
            };
            $currentSecondary = $selectedPage['created_at'];

            $getItemPrimary = function($item, $sorting) {
                return match (true) {
                    str_contains($sorting, 'updated')      => max($item['created_at'], $item['updated_at']),
                    str_contains($sorting, 'last_comment') => $item['sort_value'] ?? $item['created_at'],
                    str_contains($sorting, 'title')        => strtolower($item['title']),
                    str_contains($sorting, 'author_name')  => strtolower($item['author']),
                    str_contains($sorting, 'num_views')    => $item['num_views'],
                    str_contains($sorting, 'num_replies')  => $item['num_comments'],
                    default => $item['created_at'],
                };
            };

            $nextWhere = function($item) use ($currentPrimary, $currentSecondary, $nextPrimaryOp, $nextSecondaryOp, $getItemPrimary, $sorting, $compare) {
                $itemPrimary = $getItemPrimary($item, $sorting);
                return $compare($itemPrimary, $currentPrimary, $nextPrimaryOp) ||
                    ($itemPrimary == $currentPrimary && $compare($item['created_at'], $currentSecondary, $nextSecondaryOp));
            };

            $prevWhere = function($item) use ($currentPrimary, $currentSecondary, $prevPrimaryOp, $prevSecondaryOp, $getItemPrimary, $sorting, $compare) {
                $itemPrimary = $getItemPrimary($item, $sorting);
                return $compare($itemPrimary, $currentPrimary, $prevPrimaryOp) ||
                    ($itemPrimary == $currentPrimary && $compare($item['created_at'], $currentSecondary, $prevSecondaryOp));
            };

            $candidates = array_filter($pagesForSorting, fn($p) => $p['id'] != $selectedPage['id']);

            $nextCandidates = array_filter($candidates, $nextWhere);
            usort($nextCandidates, $cmp);
            $next = !empty($nextCandidates) ? $nextCandidates[0] : null;

            $prevCmp = function($a, $b) use ($cmp) { return $cmp($b, $a); };
            $prevCandidates = array_filter($candidates, $prevWhere);
            usort($prevCandidates, $prevCmp);
            $prev = !empty($prevCandidates) ? $prevCandidates[0] : null;

            if (empty($next) && empty($prev)) {
                continue;
            }

            $dataset[] = [
                $sorting,
                $selectedPage['id'],
                $prev['title'] ?? '',
                $prev['slug'] ?? '',
                $prev['id'] ?? 0,
                $next['title'] ?? '',
                $next['slug'] ?? '',
                $next['id'] ?? 0,
                $withinCategory,
                $pagesForSorting,
            ];
        }
    }

    return $dataset;
});

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
) use ($mockData, $allowedCategories, $cmpMap) {
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
    INSERT INTO lp_comments (id, parent_id, page_id, author_id, message, created_at) VALUES (?, ?, ?, ?, ?, ?)
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
            $page['last_comment_id']
        ]);

        $transStmt->execute([
            $page['id'],
            'page',
            'english',
            $page['title'],
            'Test content',
            'Test description'
        ]);

        if ($page['last_comment_id'] > 0) {
            $commentStmt->execute([
                $page['last_comment_id'],
                0,
                $page['id'],
                $page['author_id'],
                'Test comment',
                $page['sort_value']
            ]);
        }
    }

    User::$me->is_admin = true;
    User::$me->language = 'english';
    Config::$language = 'english';
    Utils::$smcFunc['strtolower'] = 'strtolower';
    Utils::$context['lp_current_sorting'] = $sorting;

    $links = $this->repository->getPrevNextLinks($pageData, $withinCategory);

    expect($links[0])->toBe($expectedPrevTitle)
        ->and($links[1])->toBe($expectedPrevSlug)
        ->and($links[2])->toBe($expectedNextTitle)
        ->and($links[3])->toBe($expectedNextSlug);
})->with('prev and next links');
