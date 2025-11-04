<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\CategoryArea;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\CategoryFactory;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Validators\CategoryValidator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->repositoryMock = mock(CategoryRepositoryInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->categoryArea = new CategoryArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor     = new ReflectionAccessor($this->categoryArea);

    Utils::$context += [
        'lp_category'   => [],
        'preview_title' => 'Test Title',
        'right_to_left' => false,
    ];
});

arch()
    ->expect(CategoryArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('can be instantiated', function () {
    expect($this->categoryArea)->toBeInstanceOf(CategoryArea::class);
});

it('returns correct entity name', function () {
    $result = $this->accessor->callProtectedMethod('getEntityName');

    expect($result)->toBe('category');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callProtectedMethod('getEntityNamePlural');

    expect($result)->toBe('categories');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callProtectedMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('update_priority')
        ->and($result['update_priority'])->toBeCallable();
});

it('returns correct default sort column', function () {
    $result = $this->accessor->callProtectedMethod('getDefaultSortColumn');

    expect($result)->toBe('priority');
});

it('returns correct table script', function () {
    $result = $this->accessor->callProtectedMethod('getTableScript');

    expect($result)->toBeString()
        ->and($result)->toContain('const entity = new Category();')
        ->and($result)->toContain('Sortable')
        ->and($result)->toContain('updatePriority');
});

it('returns correct table columns', function () {
    $result = $this->accessor->callProtectedMethod('getTableColumns');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(6)
        ->and($result[0])->toBeInstanceOf(IdColumn::class)
        ->and($result[1])->toBeInstanceOf(IconColumn::class)
        ->and($result[2])->toBeInstanceOf(TitleColumn::class)
        ->and($result[3])->toBeInstanceOf(Column::class)
        ->and($result[4])->toBeInstanceOf(StatusColumn::class)
        ->and($result[5])->toBeInstanceOf(ContextMenuColumn::class);
});

it('returns correct validator class', function () {
    $result = $this->accessor->callProtectedMethod('getValidatorClass');

    expect($result)->toBe(CategoryValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callProtectedMethod('getFactoryClass');

    expect($result)->toBe(CategoryFactory::class);
});

it('prepareSpecificFields can be called without errors', function () {
    $this->accessor->callProtectedMethod('prepareSpecificFields');

    expect(true)->toBeTrue();
});

it('finalizePreviewTitle can be called without errors', function () {
    $this->accessor->callProtectedMethod('finalizePreviewTitle', [['icon' => 'fas fa-test']]);

    expect(true)->toBeTrue();
});

it('getRepository returns correct instance', function () {
    $result = $this->accessor->callProtectedMethod('getRepository');

    expect($result)->toBe($this->repositoryMock);
});
