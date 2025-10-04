<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\LightPortal\UI\Partials\PageSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

use function Bugo\LightPortal\app;

beforeEach(function () {
    Lang::$txt['lp_frontpage_pages_select'] = 'Select pages';
    Lang::$txt['lp_frontpage_pages_no_items'] = 'No pages';
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'));

    $config = $select->getParams();

    expect($config['id'])->toBe('lp_frontpage_pages')
        ->and($config['multiple'])->toBeTrue()
        ->and($config['wide'])->toBeTrue()
        ->and($config['more'])->toBeTrue()
        ->and($config['value'])->toBeArray();
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_pages_id'];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_pages_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => ['1', '2']];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['value'])->toBe(['1', '2']);
});

it('initializes with custom multiple parameter', function () {
    $params = ['multiple' => false];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['multiple'])->toBeFalse();
});

it('initializes with custom wide parameter', function () {
    $params = ['wide' => false];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['wide'])->toBeFalse();
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom empty parameter', function () {
    $params = ['empty' => 'Custom empty'];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['empty'])->toBe('Custom empty');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Page', 'value' => '999']];
    $params = ['data' => $customData];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), $params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id')
        ->and($config['multiple'])->toBeTrue(); // default
});

it('returns config array', function () {
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'));

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'), []);

    expect($select->getParams())->toBeArray();
});

it('template is set correctly', function () {
    $select = new PageSelect(app('Bugo\LightPortal\Lists\PageList'));

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});
