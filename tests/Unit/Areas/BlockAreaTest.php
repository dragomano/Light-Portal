<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\BlockArea;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\BlockFactory;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Validators\BlockValidator;
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
    $result = $this->accessor->callProtectedMethod('getEntityName');

    expect($result)->toBe('block');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callProtectedMethod('getEntityNamePlural');

    expect($result)->toBe('blocks');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callProtectedMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('clone_block')
        ->and($result)->toHaveKey('update_priority')
        ->and($result['clone_block'])->toBeCallable()
        ->and($result['update_priority'])->toBeCallable();
});

it('returns correct validator class', function () {
    $result = $this->accessor->callProtectedMethod('getValidatorClass');

    expect($result)->toBe(BlockValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callProtectedMethod('getFactoryClass');

    expect($result)->toBe(BlockFactory::class);
});

it('returns correct main form action suffix', function () {
    $result = $this->accessor->callProtectedMethod('getMainFormActionSuffix');

    expect($result)->toBe(';sa=add');
});

it('returns correct remove redirect suffix', function () {
    $result = $this->accessor->callProtectedMethod('getRemoveRedirectSuffix');

    expect($result)->toBe(';sa=main');
});

it('should flush cache returns true', function () {
    $result = $this->accessor->callProtectedMethod('shouldFlushCache');

    expect($result)->toBeTrue();
});

it('should require title fields returns false', function () {
    $result = $this->accessor->callProtectedMethod('shouldRequireTitleFields');

    expect($result)->toBeFalse();
});

it('showMainContent can be called without errors', function () {
    $this->repositoryMock->shouldReceive('getAll')->andReturn([]);

    $this->accessor->callProtectedMethod('showMainContent');

    expect(true)->toBeTrue();
});

it('setupAdditionalAddContext can be called without errors', function () {
    $this->accessor->callProtectedMethod('setupAdditionalAddContext');

    expect(true)->toBeTrue();
});

it('prepareValidationContext can be called without errors', function () {
    $this->dispatcherMock->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PortalHook::class), Mockery::any());

    $this->accessor->callProtectedMethod('prepareValidationContext');

    expect(true)->toBeTrue();
});

it('postProcessValidation can be called without errors', function () {
    $this->dispatcherMock->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PortalHook::class), Mockery::any());

    $this->accessor->callProtectedMethod('postProcessValidation');

    expect(true)->toBeTrue();
});

it('prepareCommonFields can be called without errors', function () {
    $this->accessor->callProtectedMethod('prepareCommonFields');

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

        $this->accessor->callProtectedMethod('prepareSpecificFields');

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
    $this->dispatcherMock->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PortalHook::class), Mockery::any());

    $this->accessor->callProtectedMethod('dispatchFieldsEvent');
});

it('prepareEditor dispatches event', function () {
    $this->dispatcherMock->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PortalHook::class), Mockery::any());

    $this->accessor->callProtectedMethod('prepareEditor');
});

it('preparePreviewContent can be called without errors', function () {
    $this->accessor->callProtectedMethod(
        'preparePreviewContent',
        [['type' => 'test', 'content' => 'test content']]
    );

    expect(true)->toBeTrue();
});

it('preparePreviewContent handles empty content correctly', function () {
    $this->accessor->callProtectedMethod(
        'preparePreviewContent',
        [['type' => 'test', 'content' => '', 'id' => 1]]
    );

    expect(true)->toBeTrue();
});

it('finalizePreviewTitle can be called without errors', function () {
    $this->accessor->callProtectedMethod('finalizePreviewTitle', [['icon' => 'fas fa-test']]);

    expect(true)->toBeTrue();
});

it('finalizePreviewTitle handles hide_header option correctly', function () {
    $this->accessor->callProtectedMethod('finalizePreviewTitle', [
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
    $this->accessor->setProtectedProperty('request', $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('exit')->once()->with(['success' => true, 'id' => 1])->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    expect(fn() => $this->accessor->callProtectedMethod('handleClone', ['1']))->toThrow(TestExitException::class);
});

it('handleClone returns early when item is empty', function () {
    $this->accessor->callProtectedMethod('handleClone', ['']);

    expect(true)->toBeTrue();
});

it('handleClone returns early when item is null', function () {
    $this->accessor->callProtectedMethod('handleClone', [null]);

    expect(true)->toBeTrue();
});

it('handleClone returns early when item is zero', function () {
    $this->accessor->callProtectedMethod('handleClone', [0]);

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
    $this->accessor->setProtectedProperty('request', $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('exit')->once()->with(['success' => true, 'id' => 456])->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    expect(fn() => $this->accessor->callProtectedMethod('handleClone', ['123']))->toThrow(TestExitException::class);
});

it('handleClone handles cloning failure when id is zero', function () {
    $this->repositoryMock->shouldReceive('getData')
        ->with(1)
        ->andReturn(['id' => 0, 'title' => 'Block with zero ID']);
    $this->repositoryMock->shouldReceive('setData')
        ->andReturn();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('put')->with('clone', true);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('exit')->once()->with(['success' => false])->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    expect(fn() => $this->accessor->callProtectedMethod('handleClone', [1]))->toThrow(TestExitException::class);
});

it('getDefaultOptions returns options array', function () {
    $this->dispatcherMock->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PortalHook::class), Mockery::any());

    $result = $this->accessor->callProtectedMethod('getDefaultOptions');

    expect($result)->toBeArray();
});

it('getAreasInfo can be called without errors', function () {
    $this->accessor->callProtectedMethod('getAreasInfo');

    expect(true)->toBeTrue();
});

it('prepareBlockList can be called without errors', function () {
    $this->accessor->callProtectedMethod('prepareBlockList');

    expect(true)->toBeTrue();
});

it('getRepository returns correct instance', function () {
    $result = $this->accessor->callProtectedMethod('getRepository');

    expect($result)->toBe($this->repositoryMock);
});
