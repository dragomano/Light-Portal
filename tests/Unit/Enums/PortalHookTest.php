<?php

declare(strict_types=1);

use LightPortal\Assets\AssetBuilder;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\Params;

arch()
    ->expect(PortalHook::class)
    ->toBeEnum();

it('creates args for extendBasicConfig by reference', function () {
    $configVars = [['text', 'lp_test']];

    $args = PortalHook::extendBasicConfig->createArgs(['configVars' => &$configVars]);

    $configVars[] = ['check', 'lp_added'];

    expect($args->configVars)->toBe([
        ['text', 'lp_test'],
        ['check', 'lp_added'],
    ]);
});

it('creates args for extendAdminAreas by reference', function () {
    $areas = ['lp_blocks' => []];

    $args = PortalHook::extendAdminAreas->createArgs(['areas' => &$areas]);

    expect($args->areas)->toBe(['lp_blocks' => []]);
});

it('creates args for parseContent', function () {
    $content = 'initial';

    $args = PortalHook::parseContent->createArgs(['content' => &$content, 'type' => 'bbc']);

    expect($args->content)->toBe('initial')
        ->and($args->type)->toBe('bbc');
});

it('creates args for prepareIconList by reference', function () {
    $icons = ['fas fa-plus'];
    $template = '<i class="%1$s"></i>';

    $args = PortalHook::prepareIconList->createArgs(['icons' => &$icons, 'template' => &$template]);

    expect($args->icons)->toBe(['fas fa-plus'])
        ->and($args->template)->toBe('<i class="%1$s"></i>');
});

it('creates args for prepareEditor', function () {
    $object = ['type' => 'bbc', 'id' => 1];

    $args = PortalHook::prepareEditor->createArgs(['object' => $object]);

    expect($args->object)->toBe($object);
});

it('creates args for prepareAssets', function () {
    $builder = mock(AssetBuilder::class);

    $args = PortalHook::prepareAssets->createArgs(['builder' => $builder]);

    expect($args->builder)->toBe($builder);
});

it('creates args for onBlockRemoving', function () {
    $items = [1, 2, 3];

    $args = PortalHook::onBlockRemoving->createArgs(['items' => $items]);

    expect($args->items)->toBe([1, 2, 3]);
});

it('creates args for prepareContent', function () {
    $parameters = new Params(['param' => 'value']);

    $args = PortalHook::prepareContent->createArgs([
        'type'       => 'block',
        'id'         => 5,
        'cacheTime'  => 100,
        'parameters' => $parameters,
    ]);

    expect($args->type)->toBe('block')
        ->and($args->id)->toBe(5)
        ->and($args->cacheTime)->toBe(100)
        ->and($args->parameters)->toBe($parameters);
});

it('creates default args for simple hooks', function () {
    $args = PortalHook::init->createArgs();

    expect($args)->toBeObject();
});
