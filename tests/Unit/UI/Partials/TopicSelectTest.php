<?php

declare(strict_types=1);

use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\LightPortal\UI\Partials\TopicSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_frontpage_topics_select'] = 'Select topics';
    Lang::$txt['lp_frontpage_topics_no_items'] = 'No topics';

    $mockResult = Mockery::mock();
    Db::$db = Mockery::mock();
    Db::$db->shouldReceive('query')->andReturn($mockResult);
    Db::$db->shouldReceive('fetch_assoc')->with($mockResult)->andReturn(['id_topic' => 1, 'subject' => 'Topic 1'], null);
    Db::$db->shouldReceive('free_result')->with($mockResult);
});

it('implements SelectInterface', function () {
    $select = new TopicSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new TopicSelect();

    $config = $select->getParams();

    expect($config['id'])->toBe('lp_frontpage_topics')
        ->and($config['multiple'])->toBeTrue()
        ->and($config['wide'])->toBeTrue()
        ->and($config['more'])->toBeTrue()
        ->and($config['value'])->toBeArray();
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_topics_id'];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_topics_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => ['1', '2']];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['value'])->toBe(['1', '2']);
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => false];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeFalse();
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => false];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['wide'])->toBeFalse();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom empty parameter', function () {
    $params = ['empty' => 'Custom empty'];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['empty'])->toBe('Custom empty');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Topic', 'value' => '999']];
    $params = ['data' => $customData];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new TopicSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeTrue(); // default
});

it('returns config array', function () {
    $select = new TopicSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new TopicSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new TopicSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new TopicSelect([]);

    expect($select->getParams())->toBeArray();
});

it('template is set correctly', function () {
    $select = new TopicSelect();

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('topic_select');
});
