<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use LightPortal\UI\Partials\ActionSelect;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Config::$modSettings['lp_disabled_actions'] = 'action1,action2';

    Lang::$txt['no'] = 'No';
});

dataset('data scenarios', [
    'normal' => [
        'setting' => 'action1,action2',
        'expected_data' => [
            ['label' => 'action1', 'value' => 'action1'],
            ['label' => 'action2', 'value' => 'action2'],
        ],
        'expected_value' => ['action1', 'action2'],
    ],
    'empty' => [
        'setting'        => '',
        'expected_data'  => [],
        'expected_value' => [],
    ],
]);

dataset('data scenarios for labels', [
    'normal' => [
        'setting' => 'action1,action2',
        'expected_data' => [
            ['label' => 'action1', 'value' => 'action1'],
            ['label' => 'action2', 'value' => 'action2'],
        ],
    ],
    'empty' => [
        'setting' => '',
        'expected_data' => [],
    ],
]);

it('returns data with labels and values', function ($setting, $expected_data) {
    Config::$modSettings['lp_disabled_actions'] = $setting;

    $select = new ActionSelect();
    $data = $select->getData();

    expect($data)->toBe($expected_data);
})->with('data scenarios for labels');

it('checks that config includes value from data', function ($setting, $expected_data, $expected_value) {
    Config::$modSettings['lp_disabled_actions'] = $setting;

    $select = new ActionSelect();
    $config = $select->getParams();

    expect($config['value'])->toBe($expected_value);
})->with('data scenarios');

it('checks that config with custom params overrides defaults', function () {
    $params = ['id' => 'custom_actions', 'multiple' => false];
    $select = new ActionSelect($params);

    $config = $select->getParams();

    expect($config['id'])->toBe('custom_actions')
        ->and($config['multiple'])->toBeFalse()
        ->and($config['value'])->toBe(['action1', 'action2']);
});

dataset('custom params', [
    'id'        => ['param' => 'id', 'value' => 'custom_actions'],
    'value'     => ['param' => 'value', 'value' => ['action1', 'action3']],
    'multiple'  => ['param' => 'multiple', 'value' => false],
    'wide'      => ['param' => 'wide', 'value' => false],
    'allow_new' => ['param' => 'allow_new', 'value' => false],
    'hint'      => ['param' => 'hint', 'value' => 'Custom hint'],
    'empty'     => ['param' => 'empty', 'value' => 'No actions available'],
]);

it('initializes with custom parameter', function ($param, $value) {
    $params = [$param => $value];
    $select = new ActionSelect($params);

    $config = $select->getParams();

    expect($config[$param])->toBe($value);
})->with('custom params');

it('checks that the template is virtual_select', function () {
    $select = new ReflectionAccessor(new ActionSelect());
    $property = $select->getProperty('template');

    expect($property)->toBe('virtual_select');
});
