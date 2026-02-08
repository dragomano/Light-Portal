<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Partials\PlacementSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Lang::$txt['lp_block_placement_select'] = 'Select placement';

    Utils::$context['lp_block_placements'] = [
        'header' => 'Header',
        'footer' => 'Footer',
    ];
    Utils::$context['lp_block']['placement'] = 'header';
});

it('implements SelectInterface', function () {
    $select = new PlacementSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new PlacementSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('placement')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['wide'])->toBeFalse()
        ->and($config['value'])->toBe('header');
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_placement_id'];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_placement_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => 'footer'];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe('footer');
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => true];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeTrue();
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => true];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeTrue();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Placement', 'value' => 'custom']];
    $params = ['data' => $customData];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new PlacementSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeFalse(); // default
});

it('returns config array', function () {
    $select = new PlacementSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PlacementSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PlacementSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new PlacementSelect([]);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new PlacementSelect());
    $property = $select->getProperty('template');

    expect($property)->toBe('virtual_select');
});
