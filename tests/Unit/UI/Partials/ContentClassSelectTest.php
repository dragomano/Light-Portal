<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Partials\ContentClassSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['no'] = 'No';
    Utils::$context['lp_block']['content_class'] = '';
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $select = new ContentClassSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'content_class',
            'multiple' => false,
            'wide'     => false,
            'value'    => '',
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_content_class_id'],
        'expected' => [
            'id'       => 'custom_content_class_id',
            'multiple' => false,
            'wide'     => false,
            'value'    => '',
        ],
    ],
    'custom value' => [
        'params' => ['value' => 'custom_class'],
        'expected' => [
            'id'       => 'content_class',
            'multiple' => false,
            'wide'     => false,
            'value'    => 'custom_class',
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => true],
        'expected' => [
            'id'       => 'content_class',
            'multiple' => true,
            'wide'     => false,
            'value'    => '',
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => true],
        'expected' => [
            'id'       => 'content_class',
            'multiple' => false,
            'wide'     => true,
            'value'    => '',
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'content_class',
            'multiple' => false,
            'wide'     => false,
            'hint'     => 'Custom hint',
            'value'    => '',
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Class', 'value' => 'custom']]],
        'expected' => [
            'id'       => 'content_class',
            'multiple' => false,
            'wide'     => false,
            'data'     => [['label' => 'Custom Class', 'value' => 'custom']],
            'value'    => '',
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new ContentClassSelect($params);

    $config = $select->getParams();

    foreach ($expected as $key => $value) {
        expect($config[$key])->toBe($value);
    }
})->with('initialization cases');

it('returns config array', function () {
    $select = new ContentClassSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new ContentClassSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new ContentClassSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});


it('template is set correctly', function () {
    $select = new ContentClassSelect();

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('preview_select');
});
