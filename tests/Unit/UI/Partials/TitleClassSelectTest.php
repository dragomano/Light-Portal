<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Partials\TitleClassSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Lang::$txt['no'] = 'No';
    Utils::$context['lp_block']['title_class'] = '';
});

it('implements SelectInterface', function () {
    $select = new TitleClassSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new TitleClassSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('title_class')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['wide'])->toBeFalse()
        ->and($config['value'])->toBe('');
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_title_class_id'];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_title_class_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => 'custom_class'];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe('custom_class');
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => true];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeTrue();
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => true];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeTrue();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Class', 'value' => 'custom']];
    $params = ['data' => $customData];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new TitleClassSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeFalse(); // default
});

it('returns config array', function () {
    $select = new TitleClassSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new TitleClassSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new TitleClassSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new TitleClassSelect([]);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new TitleClassSelect());
    $property = $select->getProtectedProperty('template');

    expect($property)->toBe('preview_select');
});
