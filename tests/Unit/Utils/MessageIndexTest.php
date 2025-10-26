<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use LightPortal\Utils\MessageIndex;

beforeEach(function () {
    $mockMessageIndex = mock('overload:' . MessageIndex::class);
    $mockMessageIndex->shouldReceive('getBoardList')->andReturnUsing(function ($boardListOptions) {
        $recycleBoard = Config::$modSettings['recycle_board'] ?? null;

        if ($recycleBoard !== null) {
            $recycleBoard = (int) $recycleBoard;
        }

        $defaultOptions = [
            'ignore_boards'   => true,
            'use_permissions' => true,
            'not_redirection' => true,
            'excluded_boards' => $recycleBoard === null ? null : [$recycleBoard],
        ];

        if (isset($boardListOptions['included_boards'])) {
            unset($defaultOptions['excluded_boards']);
        }

        return array_merge($defaultOptions, $boardListOptions);
    });
});

afterEach(function () {
    Mockery::close();
});

it('returns board list with default options when recycle_board is null', function () {
    Config::$modSettings['recycle_board'] = null;

    $result = MessageIndex::getBoardList([]);

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'excluded_boards' => null,
    ]);
});

it('returns board list excluding recycle_board when recycle_board is set', function () {
    Config::$modSettings['recycle_board'] = '5';

    $result = MessageIndex::getBoardList([]);

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'excluded_boards' => [5],
    ]);
});

it('returns board list without excluded_boards when included_boards is passed', function () {
    Config::$modSettings['recycle_board'] = '5';

    $result = MessageIndex::getBoardList(['included_boards' => [1, 2]]);

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'included_boards' => [1, 2],
    ]);
});

it('returns board list with empty options', function () {
    Config::$modSettings['recycle_board'] = null;

    $result = MessageIndex::getBoardList([]);

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'excluded_boards' => null,
    ]);
});

it('merges options correctly with conflicting included_boards and excluded_boards', function () {
    Config::$modSettings['recycle_board'] = '5';

    $result = MessageIndex::getBoardList([
        'included_boards' => [1, 2],
        'excluded_boards' => [3, 4],
    ]);

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'included_boards' => [1, 2],
        'excluded_boards' => [3, 4],
    ]);
});
