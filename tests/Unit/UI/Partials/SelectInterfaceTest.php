<?php

declare(strict_types=1);

use Bugo\LightPortal\UI\Partials\ActionSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

it('ActionSelect implements SelectInterface', function () {
    $select = new ActionSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('SelectInterface has required methods', function () {
    $reflection = new ReflectionClass(SelectInterface::class);

    expect($reflection->hasMethod('getData'))->toBeTrue()
        ->and($reflection->hasMethod('getParams'))->toBeTrue();
});

it('implementations are stringable', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new ActionSelect();

    expect($select)->toBeInstanceOf(Stringable::class);

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});
