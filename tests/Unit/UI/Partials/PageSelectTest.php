<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\LightPortal\Lists\PageList;
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
    $select = new PageSelect(app(PageList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_pages_id'],
        'expected' => [
            'id'       => 'custom_pages_id',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['1', '2']],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'value'    => ['1', '2'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => false,
            'wide'     => true,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => false,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom empty' => [
        'params' => ['empty' => 'Custom empty'],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'empty'    => 'Custom empty',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Page', 'value' => '999']]],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'data'     => [['label' => 'Custom Page', 'value' => '999']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new PageSelect(app(PageList::class), $params);

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
    $select = new PageSelect(app(PageList::class));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PageSelect(app(PageList::class));

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PageSelect(app(PageList::class));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('template is set correctly', function () {
    $select = new PageSelect(app(PageList::class));

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});
