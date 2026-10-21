<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\BlockArea;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\BlockFactory;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Utils\DatastarSender;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Validators\BlockValidator;
use starfederation\datastar\enums\ElementPatchMode;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;
use Tests\TestExitException;

beforeEach(function () {
    $this->repositoryMock = mock(BlockRepositoryInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->blockArea = new BlockArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor  = new ReflectionAccessor($this->blockArea);

    Lang::$txt += [
        'no'               => 'No',
        'edit_permissions' => 'Permissions',
        'current_icon'     => 'Icon',
    ];

    Utils::$context += [
        'lp_block'         => ['options' => ['hide_header' => false], 'type' => 'bbc'],
        'lp_current_block' => ['type' => 'bbc'],
        'lp_content_types' => ['bbc' => 'BBC'],
        'preview_title'    => 'Test Title',
        'right_to_left'    => false,
    ];
});

arch()
    ->expect(BlockArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('can be instantiated', function () {
    expect($this->blockArea)->toBeInstanceOf(BlockArea::class);
});

it('returns correct entity name', function () {
    $result = $this->accessor->callMethod('getEntityName');

    expect($result)->toBe('block');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callMethod('getEntityNamePlural');

    expect($result)->toBe('blocks');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('clone_block')
        ->and($result)->toHaveKey('update_priority')
        ->and($result['clone_block'])->toBeCallable()
        ->and($result['update_priority'])->toBeCallable();
});

it('returns correct validator class', function () {
    $result = $this->accessor->callMethod('getValidatorClass');

    expect($result)->toBe(BlockValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callMethod('getFactoryClass');

    expect($result)->toBe(BlockFactory::class);
});

it('returns correct main form action suffix', function () {
    $result = $this->accessor->callMethod('getMainFormActionSuffix');

    expect($result)->toBe(';sa=add');
});

it('returns correct remove redirect suffix', function () {
    $result = $this->accessor->callMethod('getRemoveRedirectSuffix');

    expect($result)->toBe(';sa=main');
});

it('should flush cache returns true', function () {
    $result = $this->accessor->callMethod('shouldFlushCache');

    expect($result)->toBeTrue();
});

it('should require title fields returns false', function () {
    $result = $this->accessor->callMethod('shouldRequireTitleFields');

    expect($result)->toBeFalse();
});

it('showMainContent can be called without errors', function () {
    $this->repositoryMock->shouldReceive('getAll')->andReturn([]);

    $this->accessor->callMethod('showMainContent');

    expect(true)->toBeTrue();
});

it('setupAdditionalAddContext can be called without errors', function () {
    $this->accessor->callMethod('setupAdditionalAddContext');

    expect(true)->toBeTrue();
});

it('prepareValidationContext can be called without errors', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('prepareValidationContext');

    expect(true)->toBeTrue();
});

it('postProcessValidation can be called without errors', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('postProcessValidation');

    expect(true)->toBeTrue();
});

it('prepareCommonFields can be called without errors', function () {
    $this->accessor->callMethod('prepareCommonFields');

    expect(true)->toBeTrue();
});

describe('prepareSpecificFields', function () {
    beforeEach(function () {
        Utils::$context['lp_block'] = [
            'options' => ['content' => true, 'hide_header' => true],
            'type'    => 'html',
            'content' => 'test content',
        ];

        Utils::$context['user']['is_admin'] = false;
        Utils::$context['posting_fields'] = [];
    });

    it('creates correct fields', function ($setup, $expectations) {
        foreach ($setup as $key => $value) {
            match ($key) {
                'type'          => Utils::$context['lp_block']['type'] = $value,
                'is_admin'      => Utils::$context['user']['is_admin'] = $value,
                'link_in_title' => Utils::$context['lp_block']['options']['link_in_title'] = $value,
                default         => null,
            };
        }

        $this->accessor->callMethod('prepareSpecificFields');

        foreach ($expectations as $type => $checks) {
            match ($type) {
                'has_keys' => expect(Utils::$context['posting_fields'])->toHaveKeys($checks),
                'not_has_keys' => array_map(fn($key) => expect(Utils::$context['posting_fields'])->not->toHaveKey($key), $checks),
                'post_box_name' => is_null($checks)
                    ? expect(isset(Utils::$context['post_box_name']))->toBeFalse()
                    : expect(Utils::$context['post_box_name'])->toBe($checks),
                default => null,
            };
        }
    })->with([
        'html type creates textarea field' => [
            'setup'             => ['type' => 'html'],
            'expectations'      => [
                'post_box_name' => null,
                'has_keys'      => ['content'],
            ],
        ],
        'bbc type creates BBC field' => [
            'setup'             => ['type' => 'bbc', 'is_admin' => true],
            'expectations'      => [
                'post_box_name' => 'content',
                'has_keys'      => ['content'],
            ],
        ],
        'always creates common fields' => [
            'setup'        => ['is_admin' => true],
            'expectations' => [
                'has_keys' => [
                    'description', 'placement', 'permissions', 'areas', 'icon', 'title_class', 'hide_header',
                ],
            ],
        ],
        'with link_in_title option' => [
            'setup'              => ['link_in_title' => 'https://example.com'],
            'expectations'       => [
                'has_keys'       => ['link_in_title'],
                'link_in_title' => true,
            ],
        ],
        'without link_in_title option' => [
            'setup'              => [],
            'expectations'       => [
                'not_has_keys'  => ['link_in_title'],
                'link_in_title' => false,
            ],
        ],
    ]);
});

it('dispatchFieldsEvent dispatches event', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('dispatchFieldsEvent');
});

it('prepareEditor dispatches event', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('prepareEditor');
});

it('preparePreviewContent can be called without errors', function () {
    $this->accessor->callMethod(
        'preparePreviewContent',
        [['type' => 'test', 'content' => 'test content']]
    );

    expect(true)->toBeTrue();
});

it('preparePreviewContent handles empty content correctly', function () {
    $this->accessor->callMethod(
        'preparePreviewContent',
        [['type' => 'test', 'content' => '', 'id' => 1]]
    );

    expect(true)->toBeTrue();
});

it('finalizePreviewTitle can be called without errors', function () {
    $this->accessor->callMethod('finalizePreviewTitle', [['icon' => 'fas fa-test']]);

    expect(true)->toBeTrue();
});

it('finalizePreviewTitle handles hide_header option correctly', function () {
    $this->accessor->callMethod('finalizePreviewTitle', [
        ['icon' => 'fas fa-test', 'options' => ['hide_header' => true]]
    ]);

    expect(true)->toBeTrue();
});

it('handleClone can be called without errors', function () {
    $this->repositoryMock->shouldReceive('getData')
        ->andReturn(['id' => 1, 'title' => 'Test Block']);
    $this->repositoryMock->shouldReceive('setData')
        ->andReturn();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('put')->with('clone', true);
    $this->accessor->setProperty('request', $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('exit')->once()->with(['success' => true, 'id' => 1])->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    expect(fn() => $this->accessor->callMethod('handleClone', ['1']))->toThrow(TestExitException::class);
});

it('handleClone returns early when item is empty', function () {
    $this->accessor->callMethod('handleClone', ['']);

    expect(true)->toBeTrue();
});

it('handleClone returns early when item is null', function () {
    $this->accessor->callMethod('handleClone', [null]);

    expect(true)->toBeTrue();
});

it('handleClone returns early when item is zero', function () {
    $this->accessor->callMethod('handleClone', [0]);

    expect(true)->toBeTrue();
});

it('handleClone handles successful cloning with string item', function () {
    $this->repositoryMock->shouldReceive('getData')
        ->with(123)
        ->andReturn(['id' => 456, 'title' => 'Cloned Block']);
    $this->repositoryMock->shouldReceive('setData')
        ->andReturn();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('put')->with('clone', true);
    $this->accessor->setProperty('request', $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('exit')->once()->with(['success' => true, 'id' => 456])->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    expect(fn() => $this->accessor->callMethod('handleClone', ['123']))->toThrow(TestExitException::class);
});

it('handleClone handles cloning failure when id is zero', function () {
    $this->repositoryMock->shouldReceive('getData')
        ->with(1)
        ->andReturn(['id' => 0, 'title' => 'Block with zero ID']);
    $this->repositoryMock->shouldReceive('setData')
        ->andReturn();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('put')->with('clone', true);
    $this->accessor->setProperty('request', $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('exit')->once()->with(['success' => false])->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    expect(fn() => $this->accessor->callMethod('handleClone', [1]))->toThrow(TestExitException::class);
});

it('getDefaultOptions returns options array', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $result = $this->accessor->callMethod('getDefaultOptions');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('hide_header')
        ->and($result)->toHaveKey('content');
});

it('getAreasInfo can be called without errors', function () {
    $this->accessor->callMethod('getAreasInfo');

    expect(true)->toBeTrue();
});

it('prepareBlockList can be called without errors', function () {
    $this->accessor->callMethod('prepareBlockList');

    expect(true)->toBeTrue();
});

it('getRepository returns correct instance', function () {
    $result = $this->accessor->callMethod('getRepository');

    expect($result)->toBe($this->repositoryMock);
});

it('update_priority handler parses comma-separated ids and passes placement', function () {
    $this->repositoryMock->shouldReceive('updatePriority')
        ->once()
        ->with([3, 1, 7], 'top');

    $handlers = $this->accessor->callMethod('getCustomActionHandlers');

    $handlers['update_priority'](['update_priority' => '3,1,7', 'update_placement' => 'top']);

    expect(true)->toBeTrue();
});

it('update_priority handler ignores empty ids', function () {
    $this->repositoryMock->shouldReceive('updatePriority')
        ->once()
        ->with([5], '');

    $handlers = $this->accessor->callMethod('getCustomActionHandlers');

    $handlers['update_priority'](['update_priority' => '5,,', 'update_placement' => '']);

    expect(true)->toBeTrue();
});

describe('Datastar actions', function () {
    beforeEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);

        $this->senderMock = mock(DatastarSender::class);
        $this->accessor->setProperty('datastarSender', $this->senderMock);
    });

    afterEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);
    });

    it('handleToggle toggles status without patching for non-Datastar requests', function () {
        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(5);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggle', ['5']);

        expect(true)->toBeTrue();
    });

    it('handleToggle patches the row for Datastar requests', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(5);
        $this->repositoryMock->shouldReceive('getAll')
            ->andReturn([['placement' => 'top', 5 => ['id' => 5, 'type' => 'html', 'status' => 1]]]);
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::type('string'), 'tr[data-id="5"]');

        $this->accessor->callMethod('handleToggle', ['5']);

        expect(true)->toBeTrue();
    });

    it('handleToggle does not patch when the block is missing after toggle', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(5);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggle', ['5']);

        expect(true)->toBeTrue();
    });

    it('handleToggle returns early for empty item', function () {
        $this->repositoryMock->shouldNotReceive('toggleStatus');
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggle', ['']);

        expect(true)->toBeTrue();
    });

    it('handleRemove removes the row without Datastar patch for non-Datastar requests', function () {
        $this->repositoryMock->shouldReceive('getData')->andReturn(['placement' => 'top']);
        $this->repositoryMock->shouldReceive('remove')->once()->with(9);
        $this->senderMock->shouldNotReceive('removeElements');

        $this->accessor->callMethod('handleRemove', ['9']);

        expect(true)->toBeTrue();
    });

    it('handleRemove sends removeElements and updates the placement count', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('getData')->with(9)->andReturn(['id' => 9, 'placement' => 'top']);
        $this->repositoryMock->shouldReceive('remove')->once()->with(9);
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'top'])
            ->andReturn(1);

        $this->senderMock->shouldReceive('removeElements')->once()->with('tr[data-id="9"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="top"> (1)</span>', '[data-lp-count="top"]');

        $this->accessor->callMethod('handleRemove', ['9']);

        expect(true)->toBeTrue();
    });

    it('handleRemove shows the empty state when the placement becomes empty', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        Lang::$txt['lp_no_items'] = 'No items';

        $this->repositoryMock->shouldReceive('getData')->with(9)->andReturn(['id' => 9, 'placement' => 'bottom']);
        $this->repositoryMock->shouldReceive('remove')->once()->with(9);
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'bottom'])
            ->andReturn(0);

        $this->senderMock->shouldReceive('removeElements')->once()->with('tr[data-id="9"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="bottom"></span>', '[data-lp-count="bottom"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(
                '<tr class="windowbg centertext"><td>No items</td></tr>',
                'tbody[data-placement="bottom"]',
                ElementPatchMode::Inner
            );

        $this->accessor->callMethod('handleRemove', ['9']);

        expect(true)->toBeTrue();
    });

    it('handleRemove returns early for empty item', function () {
        $this->repositoryMock->shouldNotReceive('remove');
        $this->senderMock->shouldNotReceive('removeElements');

        $this->accessor->callMethod('handleRemove', [null]);

        expect(true)->toBeTrue();
    });

    it('handleUpdatePriority patches priorities and the placement count', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('updatePriority')->once()->with([3, 1], '')->andReturn();
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'top'])
            ->andReturn(2);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-priority>0</span>', 'tr[data-id="3"] td.priority [data-lp-priority]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-priority>1</span>', 'tr[data-id="1"] td.priority [data-lp-priority]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="top"> (2)</span>', '[data-lp-count="top"]');

        $this->accessor->callMethod('handleUpdatePriority', ['3,1', '', 'top']);

        expect(true)->toBeTrue();
    });

    it('handleUpdatePriority patches both placements when a block is moved', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('updatePriority')->once()->with([7], 'left')->andReturn();
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'left'])
            ->andReturn(1);
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'top'])
            ->andReturn(4);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-priority>0</span>', 'tr[data-id="7"] td.priority [data-lp-priority]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="left"> (1)</span>', '[data-lp-count="left"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="top"> (4)</span>', '[data-lp-count="top"]');

        $this->accessor->callMethod('handleUpdatePriority', ['7', 'left', 'top']);

        expect(true)->toBeTrue();
    });

    it('handleUpdatePriority shows the empty state in the source placement', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        Lang::$txt['lp_no_items'] = 'No items';

        $this->repositoryMock->shouldReceive('updatePriority')->once()->with([7], 'top')->andReturn();
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'top'])
            ->andReturn(5);
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'bottom'])
            ->andReturn(0);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-priority>0</span>', 'tr[data-id="7"] td.priority [data-lp-priority]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="top"> (5)</span>', '[data-lp-count="top"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="bottom"></span>', '[data-lp-count="bottom"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(
                '<tr class="windowbg centertext"><td>No items</td></tr>',
                'tbody[data-placement="bottom"]',
                ElementPatchMode::Inner
            );

        $this->accessor->callMethod('handleUpdatePriority', ['7', 'top', 'bottom']);

        expect(true)->toBeTrue();
    });

    it('handleUpdatePriority does not patch for regular requests', function () {
        $this->repositoryMock->shouldReceive('updatePriority')->once()->with([3, 1], 'top')->andReturn();
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleUpdatePriority', ['3,1', 'top', '']);

        expect(true)->toBeTrue();
    });

    it('handleClone updates the placement count for Datastar requests', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('getData')->once()->with(5)->andReturn(['id' => 5, 'placement' => 'top']);
        $this->repositoryMock->shouldReceive('setData')->andReturnUsing(function () {
            Utils::$context['lp_block']['id'] = 456;
        });
        $this->repositoryMock->shouldReceive('getAll')->andReturn([
            'top' => [
                5   => ['id' => 5, 'type' => 'html', 'status' => 1],
                456 => ['id' => 456, 'type' => 'html', 'status' => 1],
            ],
        ]);
        $this->repositoryMock->shouldReceive('getTotalCount')
            ->once()
            ->with('', ['placement = ?' => 'top'])
            ->andReturn(2);

        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('put')->with('clone', true);
        $this->accessor->setProperty('request', $requestMock);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::type('string'), 'tr[data-id="5"]', ElementPatchMode::After);
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-count="top"> (2)</span>', '[data-lp-count="top"]');

        $this->accessor->callMethod('handleClone', [5]);

        expect(true)->toBeTrue();
    });
});
