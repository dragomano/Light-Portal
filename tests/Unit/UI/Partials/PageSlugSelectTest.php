<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Lists\PageList;
use LightPortal\UI\Partials\PageSlugSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\Utils\CacheInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

use function LightPortal\app;

beforeEach(function () {
    Lang::$txt['lp_frontpage_pages_no_items'] = 'No pages';
    Lang::$txt['no'] = 'No';

    // Mock CacheInterface to execute fallback function
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('withKey')->andReturn($cacheMock);
    $cacheMock->shouldReceive('setFallback')->andReturnUsing(fn ($fallback) => $fallback());
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
});

it('implements SelectInterface', function () {
    $select = new PageSlugSelect(app(PageList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

it('initializes with default params', function () {
    $select = new PageSlugSelect(app(PageList::class));

    $config = $select->getParams();

    expect($config['id'])->toBe('lp_frontpage_chosen_page')
        ->and($config['value'])->toBeArray();
});

it('initializes with custom id parameter', function () {
    $params = ['id' => 'custom_page_slug_id'];
    $select = new PageSlugSelect(app(PageList::class), $params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_page_slug_id');
});

it('initializes with custom value parameter', function () {
    $params = ['value' => ['slug1']];
    $select = new PageSlugSelect(app(PageList::class), $params);

    $config = $select->getParams();

    expect($config['value'])->toBe(['slug1']);
});

it('initializes with custom hint parameter', function () {
    $params = ['hint' => 'Custom hint'];
    $select = new PageSlugSelect(app(PageList::class), $params);

    $config = $select->getParams();

    expect($config['hint'])->toBe('Custom hint');
});

it('initializes with custom empty parameter', function () {
    $params = ['empty' => 'Custom empty'];
    $select = new PageSlugSelect(app(PageList::class), $params);

    $config = $select->getParams();

    expect($config['empty'])->toBe('Custom empty');
});

it('initializes with custom data parameter', function () {
    $customData = [['label' => 'Custom Page', 'value' => 'slug']];
    $params = ['data' => $customData];
    $select = new PageSlugSelect(app(PageList::class), $params);

    $config = $select->getParams();

    expect($config['data'])->toBe($customData);
});

it('merges default and custom params', function () {
    $params = ['id' => 'custom_id'];
    $select = new PageSlugSelect(app(PageList::class), $params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_id');
});

it('returns config array', function () {
    $select = new PageSlugSelect(app(PageList::class));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PageSlugSelect(app(PageList::class));

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PageSlugSelect(app(PageList::class));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('handles empty params', function () {
    $select = new PageSlugSelect(app(PageList::class), []);

    expect($select->getParams())->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new PageSlugSelect(app(PageList::class)));
    $property = $select->getProperty('template');

    expect($property)->toBe('virtual_select');
});
