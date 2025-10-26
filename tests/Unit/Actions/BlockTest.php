<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Actions\Block;
use LightPortal\Actions\ActionInterface;
use LightPortal\Enums\Action;
use LightPortal\Enums\Permission;
use LightPortal\Utils\Request;
use Tests\ReflectionAccessor;

arch()
    ->expect(Block::class)
    ->toImplement(ActionInterface::class);

// Common setup for all tests
function setupBasicContext(): void
{
    Utils::$context = [
        'template_layers'  => ['html', 'body'],
        'lp_active_blocks' => [],
        'current_action'   => null,
        'current_board'    => null,
        'current_topic'    => null,
    ];
}

// Dataset for skip rendering conditions
dataset('skip rendering conditions', [
    'hide blocks in ACP' => [
        fn() => Config::$modSettings['lp_hide_blocks_in_acp'] = true,
        fn() => expect(Utils::$context)->not->toHaveKey('lp_blocks'),
    ],
    'devtools request' => [
        fn() => $_REQUEST['devtools'] = true,
        fn() => expect(Utils::$context)->not->toHaveKey('lp_blocks'),
    ],
    'preview request' => [
        fn() => $_REQUEST['preview'] = true,
        fn() => expect(Utils::$context)->not->toHaveKey('lp_blocks'),
    ],
    'empty template layers' => [
        fn() => Utils::$context['template_layers'] = [],
        fn() => expect(Utils::$context)->not->toHaveKey('lp_blocks'),
    ],
    'empty active blocks' => [
        fn() => Utils::$context['lp_active_blocks'] = [],
        fn() => expect(Utils::$context)->not->toHaveKey('lp_blocks'),
    ],
    'no view permission' => [
        fn() => User::$me->allowedTo = fn() => false,
        fn() => expect(Utils::$context)->not->toHaveKey('lp_blocks'),
    ],
]);

// Dataset for area filtering scenarios
dataset('area filtering scenarios', [
    'filter by all areas' => [
        ['all'],
        'all',
        true,
    ],
    'filter by current area' => [
        [Action::FORUM->value],
        Action::FORUM->value,
        true,
    ],
    'exclude by negative area' => [
        ['!portal'],
        'all',
        false,
    ],
    'home page special case' => [
        [Action::HOME->value],
        LP_ACTION,
        true,
    ],
    'standalone mode' => [
        ['portal'],
        'portal',
        true,
    ],
    'frontpage in standalone mode' => [
        ['forum'],
        'portal',
        false,
    ],
    'exclude by negative area when all is first' => [
        ['all', '!forum'],
        'forum',
        false,
    ],
    'exclude blocks with negative page area' => [
        ['!page=test-page'],
        '',
        false,
    ],
    'no current board and topic conditions met' => [
        ['boards'],
        '',
        false,
    ],
    'entity matching for boards and topics' => [
        ['board=5', 'topic=10'],
        '',
        false,
    ],
]);

// Dataset for page slug filtering scenarios
dataset('page slug filtering scenarios', [
    'exclude page with negative modifier' => [
        ['pages', '!page=test-page'],
        'test-page',
        false,
        'Block should be excluded when negative page modifier matches current page and tempAreas[0] is pages',
    ],
    'exclude page with negative modifier but different page' => [
        ['pages', '!page=test-page'],
        'other-page',
        true,
        'Block should be included when negative page modifier does not match current page',
    ],
    'include page with positive match' => [
        ['pages', 'page=some-page'],
        'some-page',
        true,
        'Block should be included when page slug matches positive condition',
    ],
    'include page with specific page match' => [
        [LP_PAGE_PARAM . '=exact-page'],
        'exact-page',
        true,
        'Block should be included when page slug exactly matches specified page',
    ],
    'exclude page with negative modifier and different slug' => [
        ['pages', '!page=wrong-page'],
        'correct-page',
        true,
        'Block should be included when negative modifier page does not match current page',
    ],
    'no exclusion when tempAreas not pages' => [
        ['!page=test-page', 'pages'],
        'test-page',
        true,
        'Block should be included when negative modifier exists but tempAreas[0] is not pages',
    ],
]);

// Dataset for board and topic filtering scenarios
dataset('board topic filtering scenarios', [
    'boards area with no topic' => [
        ['boards'],
        5,
        null,
        true,
        'Block should be included for boards area when current_board exists and no current_topic',
    ],
    'topics area with topic present' => [
        ['topics'],
        5,
        10,
        true,
        'Block should be included for topics area when current_topic exists',
    ],
    'board entity matching' => [
        ['board=5'],
        5,
        null,
        true,
        'Block should be included when current_board matches allowed board entities',
    ],
    'topic entity matching' => [
        ['topic=10'],
        5,
        10,
        true,
        'Block should be included when current_topic matches allowed topic entities',
    ],
    'board entity no match' => [
        ['board=3'],
        5,
        null,
        false,
        'Block should not be included when current_board does not match allowed board entities',
    ],
    'topic entity no match' => [
        ['topic=15'],
        5,
        10,
        false,
        'Block should not be included when current_topic does not match allowed topic entities',
    ],
    'no current board' => [
        ['boards'],
        null,
        null,
        false,
        'Block should not be included when no current_board exists',
    ],
]);

// Dataset for block content scenarios
dataset('block content scenarios', [
    'block with direct content' => [
        '<p>Test content</p>',
        'html',
        '<p>Test content</p>',
    ],
    'block without content' => [
        '',
        'bbc',
        '',
    ],
]);

// Dataset for title building scenarios
dataset('title build scenarios', [
    'normal title' => [
        ['title' => 'Test Block', 'icon' => 'fas fa-test', 'parameters' => []],
        '<i class="fas fa-test" aria-hidden="true"></i> Test Block',
    ],
    'hidden header' => [
        ['title' => 'Test Block', 'icon' => '', 'parameters' => ['hide_header' => true]],
        '',
    ],
    'title with link' => [
        ['title' => 'Test Block', 'icon' => 'fas fa-test', 'parameters' => ['link_in_title' => 'https://example.com']],
        '<i class="fas fa-test" aria-hidden="true"></i> <a href="https://example.com">Test Block</a>',
    ],
]);

describe('Block::show()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should skip rendering when conditions are met', function ($setup, $assertion) {
        $setup();

        $block = new Block();
        $block->show();

        $assertion();
    })->with('skip rendering conditions');

    it('should return early if getVisibleBlocks() returns empty array', function () {
        $blockMock = mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->shouldReceive('shouldSkipRendering')->andReturn(false);
        $blockMock->shouldReceive('getVisibleBlocks')->andReturn([]);
        $blockMock->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should process blocks correctly when all conditions are met', function () {
        Utils::$context['user'] = ['is_admin' => true];
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'          => 1,
                'placement'   => 'top',
                'title'       => 'Test Block',
                'icon'        => 'fas fa-test',
                'content'     => '<p>Test content</p>',
                'type'        => 'html',
                'permissions' => Permission::ALL->value,
                'parameters'  => ['hide_header' => false],
                'areas'       => ['all'],
            ]
        ];

        $blockMock = mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->shouldReceive('shouldSkipRendering')->andReturn(false);
        $blockMock->shouldReceive('getVisibleBlocks')->andReturn(Utils::$context['lp_active_blocks']);
        $blockMock->show();

        expect(Utils::$context)->toHaveKey('lp_blocks')
            ->and(Utils::$context['lp_blocks']['top'][1])->toHaveKey('title');
    });

    it('should inject portal layer correctly', function () {
        Utils::$context['user']['is_admin'] = false;
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'          => 1,
                'placement'   => 'top',
                'title'       => 'Test Block',
                'icon'        => '',
                'content'     => '',
                'type'        => 'html',
                'permissions' => Permission::ALL->value,
                'parameters'  => ['hide_header' => false],
                'areas'       => ['all'],
            ]
        ];

        $blockMock = mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->shouldReceive('shouldSkipRendering')->andReturn(false);
        $blockMock->shouldReceive('getVisibleBlocks')->andReturn([
            1 => [
                'id'          => 1,
                'placement'   => 'top',
                'title'       => 'Test Block',
                'icon'        => 'fas fa-test',
                'content'     => null,
                'type'        => 'html',
                'permissions' => Permission::ALL->value,
                'parameters'  => ['hide_header' => true],
                'areas'       => ['all'],
            ]
        ]);
        $blockMock->show();

        expect(Utils::$context['template_layers'])->toContain('lp_portal')
            ->and(Utils::$context['template_layers'])->toHaveCount(3);
    });
});

describe('Block::getFilteredByAreas()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should return empty array if lp_active_blocks is not set', function () {
        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });

    it('should return empty array when getFilteredByAreas returns empty', function () {
        $blockAccessor = new ReflectionAccessor(new Block());

        $result = $blockAccessor->callProtectedMethod('getVisibleBlocks');

        expect($result)->toBeEmpty();
    });

    it('should filter blocks correctly based on area', function ($blockAreas, $currentArea, $expected) {
        if ($blockAreas === [Action::HOME->value]) {
            Config::$modSettings['lp_frontpage_mode'] = 'all_pages';
            Utils::$context['current_action'] = null;
        } else {
            Utils::$context['current_action'] = $currentArea;
        }

        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => $blockAreas,
                'permissions' => 1,
            ]
        ];

        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        if ($expected) {
            expect($result)->toHaveKey(1);
        } else {
            expect($result)->toBeEmpty();
        }
    })->with('area filtering scenarios');

    it('should filter blocks correctly based on page slug matching', function (
        $blockAreas, $pageSlug, $expected
    ) {
        Utils::$context['lp_page'] = ['slug' => $pageSlug];
        Utils::$context['current_action'] = null;
        Utils::$context['current_board'] = null;
        Utils::$context['current_topic'] = null;

        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => $blockAreas,
                'permissions' => Permission::ALL->value,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        if ($expected) {
            expect($result)->toHaveKey(1);
        } else {
            expect($result)->toBeEmpty();
        }
    })->with('page slug filtering scenarios');

    it('should filter blocks correctly based on board and topic conditions', function (
        $blockAreas, $currentBoard, $currentTopic, $expected
    ) {
        Utils::$context['current_board'] = $currentBoard;
        Utils::$context['current_topic'] = $currentTopic;
        Utils::$context['current_action'] = null;
        Utils::$context['lp_page'] = null;

        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => $blockAreas,
                'permissions' => Permission::ALL->value,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        if ($expected) {
            expect($result)->toHaveKey(1);
        } else {
            expect($result)->toBeEmpty();
        }
    })->with('board topic filtering scenarios');
});

describe('Block::prepareBlocks()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should prepare blocks with admin privileges', function () {
        Utils::$context['user']['is_admin'] = true;
        $blocks = [
            1 => [
                'id'         => 1,
                'placement'  => 'top',
                'title'      => 'Test Block',
                'icon'       => 'fas fa-test',
                'content'    => '<p>Test</p>',
                'type'       => 'html',
                'parameters' => [],
                'areas'      => ['all'],
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $block->callProtectedMethod('prepareBlocks', [$blocks]);

        expect(Utils::$context['lp_blocks']['top'][1])->toHaveKey('can_edit')
            ->and(Utils::$context['lp_blocks']['top'][1]['can_edit'])->toBeTrue();
    });
});

describe('Block::resolveContent()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should resolve content correctly', function ($content, $type, $expected) {
        $block = [
            'content' => $content,
            'type'    => $type,
            'id'      => 1,
        ];

        Utils::$context['lp_active_blocks'][1] = ['parameters' => []];

        $blockObj = new ReflectionAccessor(new Block());
        $result = $blockObj->callProtectedMethod('resolveContent', [$block]);

        expect($result)->toBe($expected);
    })->with('block content scenarios');
});

describe('Block::buildTitle()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should build title correctly', function ($blockData, $expected) {
        $blockObj = new ReflectionAccessor(new Block());
        $result = $blockObj->callProtectedMethod('buildTitle', [$blockData]);

        expect($result)->toBe($expected);
    })->with('title build scenarios');
});

describe('Block::injectPortalLayer()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should inject portal layer when body is found', function () {
        $block = new ReflectionAccessor(new Block());
        $block->callProtectedMethod('injectPortalLayer');

        expect(Utils::$context['template_layers'])->toContain('lp_portal');
    });

    it('should not inject portal layer when body is not found', function () {
        Utils::$context['template_layers'] = ['html', 'custom'];

        $block = new ReflectionAccessor(new Block());
        $block->callProtectedMethod('injectPortalLayer');

        expect(Utils::$context['template_layers'])->not->toContain('lp_portal');
    });
});

describe('Block::resolveCurrentArea()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should resolve current area correctly in normal mode', function () {
        Utils::$context['current_action'] = Action::FORUM->value;

        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('resolveCurrentArea');

        expect($result)->toBe(Action::FORUM->value);
    });

    it('should resolve current area in standalone mode', function () {
        Config::$modSettings['lp_standalone_mode'] = true;
        Config::$modSettings['lp_standalone_url'] = 'https://example.com/test';

        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('resolveCurrentArea');

        expect($result)->toBe(Action::FORUM->value);
    });

    it('should resolve current area for pages', function () {
        Utils::$context['lp_page'] = ['slug' => 'test-page'];

        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('resolveCurrentArea');

        expect($result)->toBe('');
    });

    it('should resolve current area for frontpage', function () {
        Utils::$context['lp_page'] = ['slug' => 'frontpage'];

        Config::$modSettings['lp_frontpage_mode'] = 'chosen_page';
        Config::$modSettings['lp_frontpage_chosen_page'] = 'frontpage';

        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('resolveCurrentArea');

        expect($result)->toBe(LP_ACTION);
    });

    it('should resolve current area in standalone mode when URL matches', function () {
        Config::$modSettings['lp_standalone_mode'] = true;
        Config::$modSettings['lp_standalone_url'] = 'https://example.com/portal';

        Utils::$context['current_action'] = null;

        $requestMock = mock(Request::class);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/portal');

        $blockMock = mock(Block::class)
            ->makePartial()
            ->shouldReceive('request')
            ->andReturn($requestMock)
            ->getMock();

        $blockAccessor = new ReflectionAccessor($blockMock);
        $result = $blockAccessor->callProtectedMethod('resolveCurrentArea');

        expect($result)->toBe(LP_ACTION);
    });
});

describe('Block::getAllowedIds()', function () {
    dataset('id_formats', [
        'single id' => [
            '5',
            [5],
        ],
        'range of ids' => [
            '1-5',
            [1, 2, 3, 4, 5],
        ],
        'reverse range' => [
            '5-1',
            [5, 4, 3, 2, 1],
        ],
        'pipe separated' => [
            '1|3|5',
            [1, 3, 5],
        ],
        'mixed formats' => [
            '1-3|5|7-9',
            [1, 2, 3, 5, 7, 8, 9],
        ],
        'empty string' => [
            '',
            [],
        ],
        'empty items' => [
            '1||3',
            [1, 3],
        ],
    ]);

    it('should parse allowed ids correctly', function ($input, $expected) {
        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getAllowedIds', [$input]);

        expect($result)->toBe($expected);
    })->with('id_formats');
});

describe('Block::collectAllowedEntities()', function () {
    dataset('entity_collections', [
        'single topic and board' => [
            ['topic=1', 'board=2'],
            [
                'boards' => [2],
                'topics' => [1],
            ]
        ],
        'multiple topics and boards' => [
            ['topic=4|6', 'topic=5', 'board=1|3', 'board=2'],
            [
                'boards' => [1, 3, 2],
                'topics' => [4, 6, 5],
            ]
        ],
        'mixed formats' => [
            ['topic=1|3', 'topic=4-6', 'board=1|3', 'board=5-2'],
            [
                'boards' => [1, 3, 5, 4, 2],
                'topics' => [1, 3, 4, 5, 6],
            ]
        ],
        'empty areas' => [
            [],
            [
                'boards' => [],
                'topics' => [],
            ]
        ],
        'no entities' => [
            ['all', 'forum'],
            [
                'boards' => [],
                'topics' => [],
            ]
        ],
    ]);

    it('should collect allowed entities correctly', function ($areas, $expected) {
        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('collectAllowedEntities', [$areas]);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('boards')
            ->and($result)->toHaveKey('topics')
            ->and(array_values($result['boards']))->toBe($expected['boards'])
            ->and(array_values($result['topics']))->toBe($expected['topics']);
    })->with('entity_collections');
});

// Additional test for getVisibleBlocks to ensure permission checking
describe('Block::getVisibleBlocks()', function () {
    beforeEach(function () {
        setupBasicContext();
    });

    it('should filter blocks by permissions', function () {
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id' => 1,
                'permissions' => Permission::ALL->value,
                'areas' => ['all'],
            ],
            2 => [
                'id' => 2,
                'permissions' => Permission::ADMIN->value,
                'areas' => ['all'],
            ]
        ];

        User::$me->is_guest  = false;
        User::$me->is_admin  = false;
        User::$me->allowedTo = fn($perm) => $perm === Permission::ALL->value;

        $block  = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getVisibleBlocks');

        expect($result)->toHaveKey(1)
            ->and($result)->not->toHaveKey(2);
    });
});
