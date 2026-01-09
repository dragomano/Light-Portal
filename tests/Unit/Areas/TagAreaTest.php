<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\IdColumn;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\TagArea;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\TagFactory;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Validators\TagValidator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->repositoryMock = mock(TagRepositoryInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->tagArea  = new TagArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor = new ReflectionAccessor($this->tagArea);
});

arch()
    ->expect(TagArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('can be instantiated', function () {
    expect($this->tagArea)->toBeInstanceOf(TagArea::class);
});

it('returns correct entity name', function () {
    $result = $this->accessor->callMethod('getEntityName');

    expect($result)->toBe('tag');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callMethod('getEntityNamePlural');

    expect($result)->toBe('tags');
});

it('returns empty custom action handlers', function () {
    $result = $this->accessor->callMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns correct table columns', function () {
    $result = $this->accessor->callMethod('getTableColumns');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(5)
        ->and($result[0])->toBeInstanceOf(IdColumn::class)
        ->and($result[1])->toBeInstanceOf(IconColumn::class)
        ->and($result[2])->toBeInstanceOf(TitleColumn::class)
        ->and($result[3])->toBeInstanceOf(StatusColumn::class)
        ->and($result[4])->toBeInstanceOf(ContextMenuColumn::class);
});

it('returns correct validator class', function () {
    $result = $this->accessor->callMethod('getValidatorClass');

    expect($result)->toBe(TagValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callMethod('getFactoryClass');

    expect($result)->toBe(TagFactory::class);
});
