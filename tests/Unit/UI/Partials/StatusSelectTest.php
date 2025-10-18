<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Partials\StatusSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Lang::$txt['lp_page_status_set'] = [
        1 => 'Active',
        0 => 'Inactive',
    ];

    Utils::$context['lp_page']['status'] = 1;
});

it('implements SelectInterface', function () {
    $select = new StatusSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new StatusSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('status')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['wide'])->toBeFalse()
        ->and($config['value'])->toBe(1);
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_status_id'];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_status_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => 0];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe(0);
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => true, 'value' => '1'];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeTrue()
        ->and($config['value'])->toBe(['1']);
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => true];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeTrue();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Status', 'value' => 2]];
    $params = ['data' => $customData];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new StatusSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeFalse(); // default
});

it('returns config array', function () {
    $select = new StatusSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new StatusSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new StatusSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new StatusSelect([]);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new StatusSelect());
    $property = $select->getProtectedProperty('template');

    expect($property)->toBe('virtual_select');
});
