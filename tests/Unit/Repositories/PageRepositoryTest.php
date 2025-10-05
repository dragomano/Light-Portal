<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\PortalAdapterInterface;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Repositories\PageRepository;

use function Pest\Faker\fake;

beforeEach(function() {
    Lang::$txt['guest_title'] = 'Guest';

    Utils::$smcFunc['strtolower'] = fn($string) => strtolower($string);

    // Create repository and test
    $this->adapter = mock(PortalAdapterInterface::class);
    $this->sql = mock(PortalSql::class);
    $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
    $this->repository = new PageRepository($this->adapter);
});

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
        'last_comment_id' => $hasComments ? fake()->numberBetween(1, 1000) : 0,
        'sort_value'      => $hasComments ? fake()->numberBetween(0, $createdAt) : 0,
        'image'           => fake()->imageUrl(),
        'title'           => fake()->sentence(),
        'content'         => fake()->paragraphs(2, true),
        'description'     => fake()->sentence(),
        'options'         => [],
    ];
}

$allowedCategories = range(0, 10); // include all categories for test

$cmpMap = [
    'created;desc'      => fn($a, $b) => $b['created_at'] <=> $a['created_at'] ?: $b['id'] <=> $a['id'],
    'created'           => fn($a, $b) => $a['created_at'] <=> $b['created_at'] ?: $a['id'] <=> $b['id'],
    'updated;desc'      => fn($a, $b) => max($b['created_at'], $b['updated_at']) <=> max($a['created_at'], $a['updated_at']) ?: $b['id'] <=> $a['id'],
    'updated'           => fn($a, $b) => max($a['created_at'], $a['updated_at']) <=> max($b['created_at'], $b['updated_at']) ?: $a['id'] <=> $b['id'],
    'last_comment;desc' => fn($a, $b) => $b['sort_value'] <=> $a['sort_value'] ?: $b['id'] <=> $a['id'],
    'last_comment'      => fn($a, $b) => $a['sort_value'] <=> $b['sort_value'] ?: $a['id'] <=> $b['id'],
    'title;desc'        => fn($a, $b) => strtolower($b['title']) <=> strtolower($a['title']) ?: $b['id'] <=> $a['id'],
    'title'             => fn($a, $b) => strtolower($a['title']) <=> strtolower($b['title']) ?: $a['id'] <=> $b['id'],
    'author_name;desc'  => fn($a, $b) => $b['author'] <=> $a['author'] ?: $b['id'] <=> $a['id'],
    'author_name'       => fn($a, $b) => $a['author'] <=> $b['author'] ?: $a['id'] <=> $b['id'],
    'num_views;desc'    => fn($a, $b) => $b['num_views'] <=> $a['num_views'] ?: $b['created_at'] <=> $a['created_at'],
    'num_views'         => fn($a, $b) => $a['num_views'] <=> $b['num_views'] ?: $a['created_at'] <=> $b['created_at'],
    'num_replies;desc'  => fn($a, $b) => $b['num_comments'] <=> $a['num_comments'] ?: $b['created_at'] <=> $a['created_at'],
    'num_replies'       => fn($a, $b) => $a['num_comments'] <=> $b['num_comments'] ?: $a['created_at'] <=> $b['created_at'],
];

dataset('prev and next links', function() use ($mockData, $cmpMap, $allowedCategories) {
    $filteredAll = array_filter($mockData, function($item) use ($allowedCategories) {
        if ($item['created_at'] > time()) return false;
        if ($item['entry_type'] !== EntryType::DEFAULT->name()) return false;
        if ($item['status'] !== Status::ACTIVE->value) return false;
        if ($item['permissions'] !== Permission::ALL->value) return false;
        if (! in_array($item['category_id'], $allowedCategories)) return false;
        return true;
    });

    // Select a random page from filtered pages for debugging
    $selectedPage = $filteredAll[array_rand($filteredAll)];

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

            $pagesForSorting = $filteredAll;
            if ($withinCategory) {
                $pagesForSorting = array_filter($pagesForSorting, fn($p) => $p['category_id'] == $selectedPage['category_id']);
            }
            usort($pagesForSorting, $cmp);

            $currentIndex = null;
            foreach ($pagesForSorting as $i => $item) {
                if ($item['id'] == $selectedPage['id']) {
                    $currentIndex = $i;
                    break;
                }
            }

            $prev = $currentIndex > 0 ? $pagesForSorting[$currentIndex - 1] : null;
            $next = $currentIndex < count($pagesForSorting) - 1 ? $pagesForSorting[$currentIndex + 1] : null;

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
    $withinCategory
) use ($mockData, $allowedCategories, $cmpMap) {
    // Find the page data
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

    if ($pageData === null) {
        throw new Exception("Page with ID $pageId not found in mock data");
    }

    // Setup mocks for the existing repository
    $portalSelect = new PortalSelect(null, '');

    $this->sql->shouldReceive('select')->andReturn($portalSelect);

    $prevStatementMock = Mockery::mock();
    $prevStatementMock->shouldReceive('execute')->andReturn(Mockery::mock([
        'current' => $expectedPrevTitle !== '' ? ['page_id' => $expectedPrevId, 'slug' => $expectedPrevSlug, 'title' => $expectedPrevTitle] : null
    ]));

    $nextStatementMock = Mockery::mock();
    $nextStatementMock->shouldReceive('execute')->andReturn(Mockery::mock([
        'current' => $expectedNextTitle !== '' ? ['page_id' => $expectedNextId, 'slug' => $expectedNextSlug, 'title' => $expectedNextTitle] : null
    ]));

    $this->sql->shouldReceive('prepareStatementForSqlObject')->andReturn($prevStatementMock, $nextStatementMock);
    $this->sql->shouldReceive('getSqlStringForSqlObject')->andReturn(/** @lang text */ 'SELECT * FROM lp_pages');

    // Set user as admin to avoid DB query in Permission::all()
    User::$me->is_admin = true;

    Utils::$context['lp_current_sorting'] = $sorting;

    $links = $this->repository->getPrevNextLinks($pageData, $withinCategory);

    // Compute for debugging output
    $filteredAll = array_filter($mockData, function($item) use ($allowedCategories) {
        if ($item['created_at'] > time()) return false;
        if ($item['entry_type'] !== EntryType::DEFAULT->name()) return false;
        if ($item['status'] !== Status::ACTIVE->value) return false;
        if ($item['permissions'] !== Permission::ALL->value) return false;
        if (! in_array($item['category_id'], $allowedCategories)) return false;
        return true;
    });

    $pagesForSorting = $filteredAll;
    if ($withinCategory) {
        $pagesForSorting = array_filter($pagesForSorting, fn($p) => $p['category_id'] == $pageData['category_id']);
    }
    usort($pagesForSorting, $cmpMap[$sorting]);
    /*
    $currentIndex = null;
    foreach ($pagesForSorting as $i => $item) {
        if ($item['id'] == $pageData['id']) {
            $currentIndex = $i;
            break;
        }
    }

    $prev = $currentIndex > 0 ? $pagesForSorting[$currentIndex - 1] : null;
    $next = $currentIndex < count($pagesForSorting) - 1 ? $pagesForSorting[$currentIndex + 1] : null;

    file_put_contents('test_output.txt', json_encode([
        'sorting'        => $sorting,
        'category_id'    => $pageData['category_id'],
        'withinCategory' => $withinCategory,
        'pages'          => $pagesForSorting,
        'selected'       => $pageData,
        'prev'           => $prev,
        'next'           => $next
    ], JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
    */

    expect($links[0])->toBe($expectedPrevTitle)
        ->and($links[1])->toBe($expectedPrevSlug)
        ->and($links[2])->toBe($expectedNextTitle)
        ->and($links[3])->toBe($expectedNextSlug);
})->with('prev and next links');

