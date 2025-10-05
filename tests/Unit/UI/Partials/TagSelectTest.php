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

use function Bugo\LightPortal\app;

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
    $select = new TagSelect(app(TagList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_tags_id'],
        'expected' => [
            'id'       => 'custom_tags_id',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['1', '2']],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'value'    => ['1', '2'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'tags',
            'multiple' => false,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => false,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom empty' => [
        'params' => ['empty' => 'Custom empty'],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'empty'    => 'Custom empty',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom maxValues' => [
        'params' => ['maxValues' => 5],
        'expected' => [
            'id'        => 'tags',
            'multiple'  => true,
            'wide'      => true,
            'maxValues' => 5,
            'value'     => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Tag', 'value' => '999']]],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'data'     => [['label' => 'Custom Tag', 'value' => '999']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new TagSelect(app(TagList::class), $params);

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
    $select = new TagSelect(app(TagList::class));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new TagSelect(app(TagList::class));

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new TagSelect(app(TagList::class));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('template is set correctly', function () {
    $select = new TagSelect(app(TagList::class));

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});
