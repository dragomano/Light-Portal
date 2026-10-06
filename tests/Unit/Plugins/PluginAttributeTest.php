<?php

declare(strict_types=1);

use LightPortal\Enums\PluginType;
use LightPortal\Plugins\PluginAttribute;

it('creates with default values', function () {
    $attribute = new PluginAttribute();

    expect($attribute->type)->toBeNull()
        ->and($attribute->icon)->toBeNull()
        ->and($attribute->showSaveButton)->toBeNull()
        ->and($attribute->showContentClass)->toBeNull();
});

it('creates with a single type', function () {
    $attribute = new PluginAttribute(type: PluginType::BLOCK);

    expect($attribute->type)->toBe(PluginType::BLOCK);
});

it('creates with multiple types', function () {
    $attribute = new PluginAttribute(type: [PluginType::BLOCK, PluginType::SSI]);

    expect($attribute->type)->toBe([PluginType::BLOCK, PluginType::SSI]);
});

it('creates with additional options', function () {
    $attribute = new PluginAttribute(
        type: PluginType::EDITOR,
        icon: 'fas fa-edit',
        showSaveButton: false,
        showContentClass: false
    );

    expect($attribute->type)->toBe(PluginType::EDITOR)
        ->and($attribute->icon)->toBe('fas fa-edit')
        ->and($attribute->showSaveButton)->toBeFalse()
        ->and($attribute->showContentClass)->toBeFalse();
});

it('can be used as a class attribute', function () {
    $attributes = (new ReflectionClass(LpTestAttributedPlugin::class))
        ->getAttributes(PluginAttribute::class);

    expect($attributes)->toHaveCount(1);

    $instance = $attributes[0]->newInstance();

    expect($instance->type)->toBe(PluginType::BLOCK)
        ->and($instance->icon)->toBe('fas fa-attributed');
});

#[PluginAttribute(type: PluginType::BLOCK, icon: 'fas fa-attributed')]
class LpTestAttributedPlugin
{
}
