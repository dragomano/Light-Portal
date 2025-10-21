<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Database\PortalResultInterface;
use LightPortal\UI\Partials\TopicSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Database\Operations\PortalSelect;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Lang::$txt['lp_frontpage_topics_select'] = 'Select topics';
    Lang::$txt['lp_frontpage_topics_no_items'] = 'No topics';
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

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new TopicSelect());

	$property = $select->getProtectedProperty('template');

	expect($property)->toBe('topic_select');
});

it('returns empty array for empty topics', function () {
	$select = new TopicSelect(['value' => []]);

	$data = $select->getData();

	expect($data)->toBeArray()
		->and($data)->toBeEmpty();
});

it('returns empty array for null topics', function () {
	$select = new TopicSelect(['value' => null]);

	$data = $select->getData();

	expect($data)->toBeArray()
		->and($data)->toBeEmpty();
});

it('builds correct SQL query for topics with board permissions', function () {
	$sqlMock = Mockery::mock(PortalSqlInterface::class);
	$selectMock = Mockery::mock(PortalSelect::class);
	$resultMock = Mockery::mock(PortalResultInterface::class);

	$sqlMock->shouldReceive('select')->andReturn($selectMock);
	$sqlMock->shouldReceive('execute')->andReturn($resultMock);
	$selectMock->shouldReceive('from')->with(['t' => 'topics'])->andReturn($selectMock);
	$selectMock->shouldReceive('columns')->with(['id_topic'])->andReturn($selectMock);
	$selectMock
        ->shouldReceive('join')
        ->with(['m' => 'messages'], 'm.id_msg = t.id_first_msg', ['subject'])
        ->andReturn($selectMock);
	$selectMock->shouldReceive('where')->andReturn($selectMock);

	$resultMock->shouldReceive('rewind')->andReturn(null);
	$resultMock->shouldReceive('current')->andReturn(false);
	$resultMock->shouldReceive('next')->andReturn(null);
	$resultMock->shouldReceive('valid')->andReturn(false);

	AppMockRegistry::set(PortalSqlInterface::class, $sqlMock);

	$select = new TopicSelect(['value' => ['1', '2']]);
	$data = $select->getData();

	expect($data)->toBeArray();
});

it('builds correct SQL query for topics without board permissions', function () {
	$sqlMock = Mockery::mock(PortalSqlInterface::class);
	$selectMock = Mockery::mock(PortalSelect::class);
	$resultMock = Mockery::mock(PortalResultInterface::class);

	$sqlMock->shouldReceive('select')->andReturn($selectMock);
	$sqlMock->shouldReceive('execute')->andReturn($resultMock);
	$selectMock->shouldReceive('from')->with(['t' => 'topics'])->andReturn($selectMock);
	$selectMock->shouldReceive('columns')->with(['id_topic'])->andReturn($selectMock);
	$selectMock
        ->shouldReceive('join')
        ->with(['m' => 'messages'], 'm.id_msg = t.id_first_msg', ['subject'])
        ->andReturn($selectMock);
	$selectMock->shouldReceive('where')->andReturn($selectMock);

	$resultMock->shouldReceive('rewind')->andReturn(null);
	$resultMock->shouldReceive('current')->andReturn(false);
	$resultMock->shouldReceive('next')->andReturn(null);
	$resultMock->shouldReceive('valid')->andReturn(false);

	AppMockRegistry::set(PortalSqlInterface::class, $sqlMock);

	$select = new TopicSelect(['value' => ['1', '2']]);
	$data = $select->getData();

	expect($data)->toBeArray();
});

it('processes topic results with text censoring', function () {
	$testSubject = 'Test Topic Subject';

	$sqlMock = Mockery::mock(PortalSqlInterface::class);
	$selectMock = Mockery::mock(PortalSelect::class);
	$resultMock = Mockery::mock(PortalResultInterface::class);

	$topics = [
		['id_topic' => 1, 'subject' => $testSubject],
	];

	$sqlMock->shouldReceive('select')->andReturn($selectMock);
	$sqlMock->shouldReceive('execute')->andReturn($resultMock);
	$selectMock->shouldReceive('from')->andReturn($selectMock);
	$selectMock->shouldReceive('columns')->andReturn($selectMock);
	$selectMock->shouldReceive('join')->andReturn($selectMock);
	$selectMock->shouldReceive('where')->andReturn($selectMock);

	$resultMock->shouldReceive('rewind')->andReturn(null);
	$resultMock->shouldReceive('current')->andReturn(
		$topics[0],
		false
	);
	$resultMock->shouldReceive('next')->andReturn(null);
	$resultMock->shouldReceive('valid')->andReturn(
		true,
		false
	);

	AppMockRegistry::set(PortalSqlInterface::class, $sqlMock);

	$select = new TopicSelect(['value' => ['1']]);
	$data = $select->getData();

	expect($data)->toBeArray()
		->and($data)->toHaveCount(1)
		->and($data[0])->toHaveKey('label', $testSubject)
		->and($data[0])->toHaveKey('value', 1);
});

it('handles database errors gracefully', function () {
	$sqlMock = Mockery::mock(PortalSqlInterface::class);
	$selectMock = Mockery::mock(PortalSelect::class);

	$sqlMock->shouldReceive('select')->andReturn($selectMock);
	$sqlMock->shouldReceive('execute')->andThrow(new Exception('Database error'));
	$selectMock->shouldReceive('from')->andReturn($selectMock);
	$selectMock->shouldReceive('columns')->andReturn($selectMock);
	$selectMock->shouldReceive('join')->andReturn($selectMock);
	$selectMock->shouldReceive('where')->andReturn($selectMock);

	AppMockRegistry::set(PortalSqlInterface::class, $sqlMock);

	$select = new TopicSelect(['value' => ['1', '2']]);

	expect(fn() => $select->getData())->toThrow(Exception::class, 'Database error');
});

it('normalizes string values to array', function () {
    $select = new ReflectionAccessor(new TopicSelect(['value' => '1,2,3']));

	$result = $select->callProtectedMethod('normalizeValue', ['1,2,3']);

	expect($result)->toBe(['1', '2', '3']);
});

it('normalizes array values', function () {
    $select = new ReflectionAccessor(new TopicSelect(['value' => ['1', '2', '3']]));

	$result = $select->callProtectedMethod('normalizeValue', [['1', '2', '3']]);

	expect($result)->toBe(['1', '2', '3']);
});

it('filters empty values in normalization', function () {
    $select = new ReflectionAccessor(new TopicSelect(['value' => ['0', '1', '', '2', null, '3']]));

	$result = $select->callProtectedMethod('normalizeValue', [['0', '1', '', '2', null, '3']]);

	expect($result)->toBe(['0', '1', '2', '', '3']);
});
