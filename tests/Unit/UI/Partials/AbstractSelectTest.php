<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use LightPortal\UI\Partials\ActionSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Config::$modSettings['lp_disabled_actions'] = 'action1,action2';

    Lang::$txt['lp_example'] = 'Example: ';
    Lang::$txt['no'] = 'No';
});

it('implements SelectInterface', function () {
    $select = new ActionSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'lp_disabled_actions',
            'multiple' => true,
            'wide'     => true,
            'allowNew' => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom params' => [
        'params' => ['id' => 'custom_id', 'multiple' => false],
        'expected' => [
            'id'       => 'custom_id',
            'multiple' => false,
            'wide'     => true,
            'allowNew' => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'merge default and custom params' => [
        'params' => ['id' => 'custom_id'],
        'expected' => [
            'id'       => 'custom_id',
            'multiple' => true,
            'wide'     => true,
            'allowNew' => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new ActionSelect($params);

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
    $select = new ActionSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new ActionSelect();

    $data = $select->getData();

    expect($data)->toBeArray()
        ->and(count($data))->toBe(2)
        ->and($data[0])->toHaveKey('label')
        ->and($data[0])->toHaveKey('value')
        ->and($data[0]['label'])->toBe('action1')
        ->and($data[0]['value'])->toBe('action1');
});

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new ActionSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new ActionSelect());
    $property = $select->getProperty('template');

    expect($property)->toBe('virtual_select');
});
