<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\LightPortal\UI\Partials\BoardSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Bugo\LightPortal\Utils\MessageIndex;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_frontpage_boards_select'] = 'Select boards';

    Config::$modSettings['recycle_board'] = null;

    $mockMessageIndex = Mockery::mock('overload:' . MessageIndex::class);
    $mockMessageIndex->shouldReceive('getBoardList')->andReturn([
        [
            'name' => 'Category 1',
            'boards' => [
                1 => ['name' => 'Board 1'],
                2 => ['name' => 'Board 2'],
            ]
        ]
    ]);
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $select = new BoardSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'lp_frontpage_boards',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_boards_id'],
        'expected' => [
            'id'       => 'custom_boards_id',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['1', '2']],
        'expected' => [
            'id'       => 'lp_frontpage_boards',
            'multiple' => true,
            'wide'     => true,
            'value'    => ['1', '2'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'lp_frontpage_boards',
            'multiple' => false,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'lp_frontpage_boards',
            'multiple' => true,
            'wide'     => false,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'lp_frontpage_boards',
            'multiple' => true,
            'wide'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Board', 'value' => '999']]],
        'expected' => [
            'id'       => 'lp_frontpage_boards',
            'multiple' => true,
            'wide'     => true,
            'data'     => [['label' => 'Custom Board', 'value' => '999']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new BoardSelect($params);

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
    $select = new BoardSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new BoardSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new BoardSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('template is set correctly', function () {
    $select = new BoardSelect();

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});
