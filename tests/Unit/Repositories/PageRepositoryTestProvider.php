<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Bugo\Compat\User;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;

use function Pest\Faker\fake;

class PageRepositoryTestProvider
{
    public function generatePrevNextTestData(): array
    {
        User::$me = new User(1);
        User::$me->language = 'english';
        User::$me->groups = [];

        fake()->seed(12345); // Ensure consistent fake data across runs
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

            'title;desc' => fn($a, $b) => ascii_strtolower($b['title']) <=> ascii_strtolower($a['title'])
                ?: $b['created_at'] <=> $a['created_at']
                ?: $b['id'] <=> $a['id'],

            'title' => fn($a, $b) => ascii_strtolower($a['title']) <=> ascii_strtolower($b['title'])
                ?: $a['created_at'] <=> $b['created_at']
                ?: $a['id'] <=> $b['id'],

            'author_name;desc' => fn($a, $b) => ascii_strtolower($b['author']) <=> ascii_strtolower($a['author'])
                ?: $b['created_at'] <=> $a['created_at']
                ?: $b['id'] <=> $a['id'],

            'author_name' => fn($a, $b) => ascii_strtolower($a['author']) <=> ascii_strtolower($b['author'])
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

            if (! in_array($item['permissions'], Permission::all())) {
                return false;
            }

            if (! in_array($item['category_id'], $allowedCategories)) {
                return false;
            }

            if (empty($item['title'])) {
                return false;
            }

            return true;
        });

        $filteredAll = array_values($filteredAll);
        $selectedPages = array_slice($filteredAll, 0, 10);
        $selectedPage = $selectedPages[2]; // Always select third page for deterministic behavior

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

                $listAsc = ! str_contains($sorting, ';desc');
                $nextPrimaryOp = $listAsc ? '>' : '<';
                $nextSecondaryOp = $listAsc ? '>' : '<';
                $prevPrimaryOp = $listAsc ? '<' : '>';
                $prevSecondaryOp = $listAsc ? '<' : '>';

                $currentPrimary = match (true) {
                    str_contains($sorting, 'updated')      => max($selectedPage['created_at'], $selectedPage['updated_at']),
                    str_contains($sorting, 'last_comment') => $selectedPage['sort_value'] ?? $selectedPage['created_at'],
                    str_contains($sorting, 'title')        => ascii_strtolower($selectedPage['title']),
                    str_contains($sorting, 'author_name')  => ascii_strtolower($selectedPage['author']),
                    str_contains($sorting, 'num_views')    => $selectedPage['num_views'],
                    str_contains($sorting, 'num_replies')  => $selectedPage['num_comments'],
                    default => $selectedPage['created_at'],
                };
                $currentSecondary = $selectedPage['created_at'];

                $getItemPrimary = function($item, $sorting) {
                    return match (true) {
                        str_contains($sorting, 'updated')      => max($item['created_at'], $item['updated_at']),
                        str_contains($sorting, 'last_comment') => $item['sort_value'] ?? $item['created_at'],
                        str_contains($sorting, 'title')        => ascii_strtolower($item['title']),
                        str_contains($sorting, 'author_name')  => ascii_strtolower($item['author']),
                        str_contains($sorting, 'num_views')    => $item['num_views'],
                        str_contains($sorting, 'num_replies')  => $item['num_comments'],
                        default => $item['created_at'],
                    };
                };

                $nextWhere = function ($item) use (
                    $currentPrimary,
                    $currentSecondary,
                    $nextPrimaryOp,
                    $nextSecondaryOp,
                    $getItemPrimary,
                    $sorting,
                    $compare
                ) {
                    $itemPrimary = $getItemPrimary($item, $sorting);

                    return $compare($itemPrimary, $currentPrimary, $nextPrimaryOp) ||
                        ($itemPrimary == $currentPrimary && $compare($item['created_at'], $currentSecondary, $nextSecondaryOp));
                };

                $prevWhere = function ($item) use (
                    $currentPrimary,
                    $currentSecondary,
                    $prevPrimaryOp,
                    $prevSecondaryOp,
                    $getItemPrimary,
                    $sorting,
                    $compare
                ) {
                    $itemPrimary = $getItemPrimary($item, $sorting);

                    return $compare($itemPrimary, $currentPrimary, $prevPrimaryOp) ||
                        ($itemPrimary == $currentPrimary && $compare($item['created_at'], $currentSecondary, $prevSecondaryOp));
                };

                $candidates = array_filter($pagesForSorting, fn($p) => $p['id'] != $selectedPage['id']);

                $nextCandidates = array_filter($candidates, $nextWhere);
                usort($nextCandidates, $cmp);
                $next = ! empty($nextCandidates) ? $nextCandidates[0] : null;

                $prevCmp = function($a, $b) use ($cmp) { return $cmp($b, $a); };
                $prevCandidates = array_filter($candidates, $prevWhere);
                usort($prevCandidates, $prevCmp);
                $prev = ! empty($prevCandidates) ? $prevCandidates[0] : null;

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

        return [$dataset, $mockData, $cmpMap, $allowedCategories];
    }
}
