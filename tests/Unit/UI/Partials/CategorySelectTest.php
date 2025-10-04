<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\UI\Partials\CategorySelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_frontpage_categories_select'] = 'Select categories';
});

afterEach(function () {
    Mockery::close();
});

$mockCategoryList = Mockery::mock(CategoryList::class);
$mockCategoryList->shouldReceive('__invoke')->andReturn([]);

it('implements SelectInterface', function () {
    $mockCategoryList = Mockery::mock(CategoryList::class);
    $mockCategoryList->shouldReceive('__invoke')->andReturn([]);

    $select = new CategorySelect($mockCategoryList);

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
    $mockCategoryList = Mockery::mock(CategoryList::class);
    $mockCategoryList->shouldReceive('__invoke')->andReturn([]);

    $select = new CategorySelect($mockCategoryList, $params);

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
    $mockCategoryList = Mockery::mock(CategoryList::class);
    $mockCategoryList->shouldReceive('__invoke')->andReturn([]);

    $select = new CategorySelect($mockCategoryList);

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $mockCategoryList = Mockery::mock(CategoryList::class);
    $mockCategoryList->shouldReceive('__invoke')->andReturn([]);

    $select = new CategorySelect($mockCategoryList);

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockCategoryList = Mockery::mock(CategoryList::class);
    $mockCategoryList->shouldReceive('__invoke')->andReturn([]);

    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new CategorySelect($mockCategoryList);

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});


it('template is set correctly', function () {
    $mockCategoryList = Mockery::mock(CategoryList::class);
    $mockCategoryList->shouldReceive('__invoke')->andReturn([]);

    $select = new CategorySelect($mockCategoryList);

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});
