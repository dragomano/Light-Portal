<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\UI\Partials\TagSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_page_tags_placeholder'] = 'Select tags';
    Lang::$txt['lp_page_tags_empty'] = 'No tags';

    Utils::$context['lp_page']['tags'] = [
        1 => ['title' => 'Tag1'],
        2 => ['title' => 'Tag2'],
    ];

    Config::$modSettings['lp_page_maximum_tags'] = 5;
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $select = new TagSelect($mockTagList);

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $select = new TagSelect($mockTagList);

    $config = $select->getParams();

    expect($config['id'])->toBe('tags')
        ->and($config['multiple'])->toBeTrue()
        ->and($config['wide'])->toBeTrue()
        ->and($config['value'])->toBeArray();
});

it('initializes with custom id parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['id' => 'custom_tags_id'];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_tags_id');
});

it('initializes with custom value parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['value' => ['1', '2']];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['value'])->toBe(['1', '2']);
});

it('initializes with custom multiple parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['multiple' => false];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeFalse();
});

it('initializes with custom wide parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['wide' => false];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['wide'])->toBeFalse();
});

it('initializes with custom hint parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['hint' => 'Custom hint'];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom empty parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['empty' => 'Custom empty'];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['empty'])->toBe('Custom empty');
});

it('initializes with custom max_values parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['max_values' => 5];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['max_values'])->toBe(5);
});

it('initializes with custom data parameter', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $customData = [['label' => 'Custom Tag', 'value' => '999']];
    $params = ['data' => $customData];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $params = ['id' => 'custom_id'];
    $select = new TagSelect($mockTagList, $params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeTrue(); // default
});

it('returns config array', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $select = new TagSelect($mockTagList);

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $select = new TagSelect($mockTagList);

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new TagSelect($mockTagList);

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $select = new TagSelect($mockTagList, []);

    expect($select->getParams())->toBeArray();
});

it('template is set correctly', function () {
    $mockTagList = Mockery::mock(TagList::class);
    $mockTagList->shouldReceive('__invoke')->andReturn([]);

    $select = new TagSelect($mockTagList);

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});
