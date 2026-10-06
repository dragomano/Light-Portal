<?php

declare(strict_types=1);

use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SsiBlock;

it('is an abstract block with SSI support', function () {
    $reflection = new ReflectionClass(SsiBlock::class);

    expect($reflection->isAbstract())->toBeTrue()
        ->and($reflection->isSubclassOf(Block::class))->toBeTrue();
});

it('declares block and ssi types in its attribute', function () {
    $attributes = (new ReflectionClass(SsiBlock::class))->getAttributes(PluginAttribute::class);

    expect($attributes)->toHaveCount(1);

    $instance = $attributes[0]->newInstance();

    expect($instance->type)->toBe([PluginType::BLOCK, PluginType::SSI]);
});

it('requires prepareBlockFields and prepareContent methods', function () {
    $reflection = new ReflectionClass(SsiBlock::class);

    foreach (['prepareBlockFields', 'prepareContent'] as $method) {
        expect($reflection->hasMethod($method))->toBeTrue()
            ->and($reflection->getMethod($method)->isAbstract())->toBeTrue();
    }
});
