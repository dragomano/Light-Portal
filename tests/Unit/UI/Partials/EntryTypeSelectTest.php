<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\Enums\EntryType;
use LightPortal\UI\Partials\EntryTypeSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Utils::$context['lp_page_types'] = [
        EntryType::DEFAULT->name() => 'Default',
        EntryType::INTERNAL->name() => 'Internal',
    ];
    Utils::$context['user']['is_admin'] = true;
    Utils::$context['lp_page']['entry_type'] = EntryType::DEFAULT->name();
});

it('implements SelectInterface', function () {
    $select = new EntryTypeSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new EntryTypeSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('entry_type')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['wide'])->toBeFalse()
        ->and($config['value'])->toBe(EntryType::DEFAULT->name());
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_entry_type_id'];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_entry_type_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => EntryType::INTERNAL->name()];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe(EntryType::INTERNAL->name());
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => true];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeTrue();
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => true];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeTrue();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Type', 'value' => 'custom']];
    $params = ['data' => $customData];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new EntryTypeSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeFalse(); // default
});

it('returns config array', function () {
    $select = new EntryTypeSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new EntryTypeSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new EntryTypeSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new EntryTypeSelect([]);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new EntryTypeSelect());
    $property = $select->getProtectedProperty('template');

    expect($property)->toBe('virtual_select');
});
