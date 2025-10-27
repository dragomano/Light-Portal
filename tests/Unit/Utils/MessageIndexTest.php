<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use LightPortal\Utils\MessageIndex;

it('returns board list with default options when recycle_board is null', function () {
    Config::$modSettings['recycle_board'] = null;

    $result = MessageIndex::getBoardList();

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'excluded_boards' => null,
    ]);
});

it('returns board list excluding recycle_board when recycle_board is set', function () {
    Config::$modSettings['recycle_board'] = '5';

    $result = MessageIndex::getBoardList();

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'excluded_boards' => [5],
    ]);
});

it('returns board list with empty options', function () {
    Config::$modSettings['recycle_board'] = null;

    $result = MessageIndex::getBoardList();

    expect($result)->toBe([
        'ignore_boards'   => true,
        'use_permissions' => true,
        'not_redirection' => true,
        'excluded_boards' => null,
    ]);
});
