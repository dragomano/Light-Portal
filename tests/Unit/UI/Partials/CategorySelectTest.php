<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Lists\CategoryList;
use LightPortal\UI\Partials\CategorySelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\Utils\CacheInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

use function LightPortal\app;

beforeEach(function () {
    Lang::$txt['lp_frontpage_categories_select'] = 'Select categories';
    Lang::$txt['lp_no_category'] = 'No category';

    // Mock CacheInterface to execute fallback function
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('withKey')->andReturn($cacheMock);
    $cacheMock->shouldReceive('setFallback')->andReturnUsing(fn ($fallback) => $fallback());
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
});

it('implements SelectInterface', function () {
    $select = new CategorySelect(app(CategoryList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_categories_id'],
        'expected' => [
            'id'       => 'custom_categories_id',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['1', '2']],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => true,
            'wide'     => true,
            'value'    => ['1', '2'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => false,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => true,
            'wide'     => false,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => true,
            'wide'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom disabled' => [
        'params' => ['disabled' => true],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => true,
            'wide'     => true,
            'disabled' => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Category', 'value' => '999']]],
        'expected' => [
            'id'       => 'lp_frontpage_categories',
            'multiple' => true,
            'wide'     => true,
            'data'     => [['label' => 'Custom Category', 'value' => '999']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new CategorySelect(app(CategoryList::class), $params);

    $config = $select->getParams();

    foreach ($expected as $key => $value) {
        if (is_callable($value)) {
            expect($value($config[$key]))->toBeTrue();
        } else {
            expect($config[$key])->toBe($value);
        }
    }
})->with('initialization cases');

it('returns config array', function () {
    $select = new CategorySelect(app(CategoryList::class));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new CategorySelect(app(CategoryList::class));

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new CategorySelect(app(CategoryList::class));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new CategorySelect(app(CategoryList::class)));
    $property = $select->getProperty('template');

    expect($property)->toBe('virtual_select');
});
