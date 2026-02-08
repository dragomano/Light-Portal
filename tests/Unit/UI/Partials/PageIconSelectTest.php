<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\Partials\PageIconSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Utils::$context['lp_page']['options']['page_icon'] = 'fas fa-star';
    Utils::$context['lp_page']['options']['show_in_menu'] = true;
});

it('implements SelectInterface', function () {
    $select = new PageIconSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new PageIconSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('page_icon')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['wide'])->toBeFalse()
        ->and($config['value'])->toBe('fas fa-star');
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_page_icon_id'];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_page_icon_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => 'fas fa-heart'];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe('fas fa-heart');
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => true];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeTrue();
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => true];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeTrue();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom disabled parameter', function () {
    $params = ['disabled' => true];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['disabled'])->toBeTrue();
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Icon', 'value' => 'fas fa-custom']];
    $params = ['data' => $customData];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new PageIconSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeFalse(); // default
});

it('returns config array', function () {
    $select = new PageIconSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PageIconSelect();

    $data = $select->getData();

    expect($data)->toBeArray()
        ->and(count($data))->toBe(1);
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PageIconSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new PageIconSelect([]);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new PageIconSelect());
    $property = $select->getProperty('template');

    expect($property)->toBe('page_icon_select');
});
