<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\Block;
use Bugo\LightPortal\Actions\ActionInterface;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Enums\Permission;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(Block::class)
    ->toImplement(ActionInterface::class);

describe('Block::show()', function () {
    beforeEach(function () {
        Utils::$context = [
            'template_layers'  => ['html', 'body'],
            'lp_active_blocks' => [],
            'user'             => ['is_admin' => false],
            'current_action'   => null,
            'lp_page'          => [],
            'current_board'    => null,
            'current_topic'    => null,
        ];

        AppMockRegistry::clear();
        $_REQUEST = [];
    });

    it('should return early if Setting::hideBlocksInACP() returns true', function () {
        Config::$modSettings['lp_hide_blocks_in_acp'] = true;

        $block = new Block();
        $block->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should return early if request()->is("devtools") returns true', function () {
        $_REQUEST['devtools'] = true;

        $block = new Block();
        $block->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should return early if request()->has("preview") returns true', function () {
        $_REQUEST['preview'] = true;

        $block = new Block();
        $block->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should return early if template_layers is empty', function () {
        Utils::$context['template_layers'] = [];

        $block = new Block();
        $block->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should return early if lp_active_blocks is empty', function () {
        Utils::$context['lp_active_blocks'] = [];

        $block = new Block();
        $block->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should return early if user has no permission to view', function () {
        Utils::$context['user'] = ['is_admin' => false];
        Utils::$context['lp_active_blocks'] = [];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toBeEmpty();
    });

    it('should return early if getFilteredByAreas() returns empty array', function () {
        Utils::$context['lp_active_blocks'] = [1 => ['id' => 1]];

        User::$me = new User(1);
        User::$me->allowedTo = fn(...$params) => true;

        $blockMock = Mockery::mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->shouldReceive('getFilteredByAreas')->once()->andReturn([]);
        $blockMock->shouldReceive('resolveCurrentArea')->andReturn('forum');
        $blockMock->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should skip blocks if Permission::canViewItem() returns false', function () {
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'          => 1,
                'placement'   => 'top',
                'title'       => 'Test Block',
                'icon'        => 'fas fa-test',
                'content'     => '',
                'type'        => 'html',
                'permissions' => Permission::ADMIN->value,
                'parameters'  => ['hide_header' => false],
                'areas'       => ['all'],
            ]
        ];

        User::$me = new User(1);
        User::$me->is_admin = false;
        User::$me->is_guest = true;

        $blockMock = Mockery::mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->shouldReceive('getFilteredByAreas')->once()->andReturn(Utils::$context['lp_active_blocks']);
        $blockMock->show();

        expect(Utils::$context)->not->toHaveKey('lp_blocks');
    });

    it('should process blocks correctly', function () {
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

        User::$me = new User(1);
        User::$me->allowedTo = fn(...$params) => true;
        User::$me->is_admin = true;

        $blockMock = Mockery::mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->show();

        expect(Utils::$context)->toHaveKey('lp_blocks')
            ->and(Utils::$context['lp_blocks']['top'][1])->toHaveKey('title');
    });

    it('should add blocks to lp_portal layer', function () {
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

        User::$me = new User(1);
        User::$me->allowedTo = fn(...$params) => true;
        User::$me->is_admin = true;

        $blockMock = Mockery::mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->shouldReceive('getFilteredByAreas')->andReturn([
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

    it('should process block with hidden header', function () {
        Utils::$context['user']['is_admin'] = true;
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'          => 1,
                'placement'   => 'top',
                'title'       => 'Test Block',
                'icon'        => 'fas fa-test',
                'content'     => '',
                'type'        => 'html',
                'permissions' => Permission::ALL->value,
                'parameters'  => ['hide_header' => true],
                'areas'       => ['all'],
            ]
        ];

        User::$me = new User(1);
        User::$me->allowedTo = fn(...$params) => true;
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;

        $block = new Block();
        $block->show();

        expect(Utils::$context['lp_blocks']['top'][1]['title'])->toBe('');
    });

    it('should process block with link in title', function () {
        Utils::$context['user']['is_admin'] = true;
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'          => 1,
                'placement'   => 'top',
                'title'       => 'Test Block',
                'icon'        => 'fas fa-test',
                'content'     => '',
                'type'        => 'html',
                'permissions' => Permission::ALL->value,
                'parameters'  => [
                    'hide_header'   => false,
                    'link_in_title' => 'https://example.com',
                ],
                'areas'       => ['all'],
            ]
        ];

        User::$me = new User(1);
        User::$me->allowedTo = fn(...$params) => true;
        User::$me->is_admin = true;

        $blockMock = Mockery::mock(Block::class)->makePartial();
        $blockMock->shouldAllowMockingProtectedMethods();
        $blockMock->show();

        expect(Utils::$context['lp_blocks']['top'][1]['title'])->toContain('https://example.com');
    });
});

describe('Block::getFilteredByAreas()', function () {
    beforeEach(function () {
        Utils::$context = [
            'lp_active_blocks' => [],
            'current_action'   => null,
            'lp_page'          => [],
            'current_board'    => null,
            'current_topic'    => null,
        ];
    });

    it('should return empty array if lp_active_blocks is not set', function () {
        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });

    it('should filter blocks by "all" area', function () {
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['all'],
                'permissions' => 1,
            ],
            2 => [
                'id'    => 2,
                'areas' => ['forum'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1)
            ->and($result)->not->toHaveKey(2);
    });

    it('should filter blocks by current area', function () {
        unset(Utils::$context['current_board'], Utils::$context['lp_page']);

        Utils::$context['current_action'] = 'forum';
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' =>  ['forum'],
                'permissions' => 1,
            ],
            2 => [
                'id'    => 2,
                'areas' =>  ['portal'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1)
            ->and($result)->not->toHaveKey(2);
    });

    it('should filter blocks by home page', function () {
        Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

        unset(Utils::$context['current_board'], Utils::$context['lp_page']);

        Utils::$context['current_action'] = 'home';
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' =>  [Action::HOME->value],
                'permissions' => 1,
            ],
            2 => [
                'id'    => 2,
                'areas' =>  ['forum'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1)
            ->and($result)->not->toHaveKey(2);
    });

    it('should exclude blocks with negative area', function () {
        Utils::$context['current_action'] = 'portal';
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['!portal'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toBeEmpty();
    });

    it('should filter blocks by pages', function () {
        Utils::$context['lp_page'] = ['slug' => 'test-page'];
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['pages'],
                'permissions' => 1,
            ],
            2 => [
                'id'    => 2,
                'areas' => ['page=test-page'],
                'permissions' => 1,
            ],
            3 => [
                'id'    => 3,
                'areas' => ['forum'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1)
            ->and($result)->toHaveKey(2)
            ->and($result)->not->toHaveKey(3);
    });

    it('should filter blocks by boards', function () {
        Utils::$context['current_board'] = 5;
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['boards'],
                'permissions' => 1,
            ],
            2 => [
                'id'    => 2,
                'areas' => ['board=5'],
                'permissions' => 1,
            ],
            3 => [
                'id'    => 3,
                'areas' => ['forum'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1)
            ->and($result)->toHaveKey(2)
            ->and($result)->not->toHaveKey(3);
    });

    it('should filter blocks by topics', function () {
        Utils::$context['current_board'] = 5;
        Utils::$context['current_topic'] = 10;
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['topics'],
                'permissions' => 1,
            ],
            2 => [
                'id'    => 2,
                'areas' => ['topic=10'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1)
            ->and($result)->toHaveKey(2);
    });

    it('should work in standalone mode', function () {
        Config::$modSettings['lp_frontpage_mode'] = 'all_pages';
        Config::$modSettings['lp_standalone_mode'] = true;
        Config::$modSettings['lp_standalone_url'] = 'https://example.com/portal';

        unset(Utils::$context['current_board'], Utils::$context['lp_page']);

        Utils::$context['current_action'] = 'portal';
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['portal'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->toHaveKey(1);
    });

    it('should work with frontpage in standalone mode', function () {
        Config::$modSettings['lp_frontpage_mode'] = 'all_pages';
        Config::$modSettings['lp_standalone_mode'] = true;
        Config::$modSettings['lp_standalone_url'] = 'https://example.com/portal';

        unset(Utils::$context['current_board'], Utils::$context['lp_page']);

        Utils::$context['current_action'] = 'portal';
        Utils::$context['lp_active_blocks'] = [
            1 => [
                'id'    => 1,
                'areas' => ['forum'],
                'permissions' => 1,
            ]
        ];

        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('getFilteredByAreas');

        expect($result)->not->toHaveKey(1);
    });
});

describe('Block::collectAllowedEntities()', function () {
    dataset('boards and topics', [
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
    ]);

    it('should handle collectAllowedEntities with various area formats', function ($areas, $expected) {
        $block = new ReflectionAccessor(new Block());
        $result = $block->callProtectedMethod('collectAllowedEntities', [$areas]);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('boards')
            ->and($result)->toHaveKey('topics')
            ->and(array_values($result['boards']))->toBe($expected['boards'])
            ->and(array_values($result['topics']))->toBe($expected['topics']);
    })->with('boards and topics');
});
