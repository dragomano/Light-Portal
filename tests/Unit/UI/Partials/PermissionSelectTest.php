<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Partials\PermissionSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Lang::$txt['lp_permissions'] = [
        0 => 'Admins only',
        1 => 'Guests only',
        2 => 'Memmbers only',
        3 => 'Everybody',
    ];

    Utils::$context['user']['is_admin'] = true;
    Utils::$context['lp_page']['permissions'] = 0;
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $select = new PermissionSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new PermissionSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('permissions')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['wide'])->toBeFalse()
        ->and($config['value'])->toBe(0);
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_permissions_id'];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_permissions_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => 1];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe(1);
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => true, 'value' => '0'];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeTrue()
        ->and($config['value'])->toBe(['0']);
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => true];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeTrue();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom type parameter', function () {
    $params = ['type' => 'block'];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['type'])->toBe('block');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Permission', 'value' => 3]];
    $params = ['data' => $customData];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new PermissionSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeFalse(); // default
});

it('returns config array', function () {
    $select = new PermissionSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PermissionSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PermissionSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new PermissionSelect([]);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new PermissionSelect());
    $property = $select->getProtectedProperty('template');

    expect($property)->toBe('virtual_select');
});
