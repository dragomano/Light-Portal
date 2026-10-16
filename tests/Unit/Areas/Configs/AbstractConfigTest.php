<?php

declare(strict_types=1);

use Bugo\Bricks\Settings\CheckConfig;
use Bugo\Bricks\Settings\ConfigBuilder;
use Bugo\Bricks\Settings\DividerConfig;
use Bugo\Bricks\Settings\SelectConfig;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\Configs\AbstractConfig;
use LightPortal\Areas\Configs\ConfigInterface;
use LightPortal\Areas\Traits\HasArea;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Config::$modSettings = [];

    Lang::$txt['permissionname_lp_test_permissions'] = 'Test Permissions Label';

    Utils::$context['config_vars'] = [];
    Utils::$context['posting_fields'] = [];

    $this->config = new class extends AbstractConfig
    {
        public function show(): void
        {
        }
    };
});

arch()
    ->expect(AbstractConfig::class)
    ->toBeAbstract()
    ->toImplement(ConfigInterface::class)
    ->toUseTrait(HasArea::class);

it('adds default values to mod settings', function () {
    $reflection = new ReflectionAccessor($this->config);

    // Set up existing settings
    Config::$modSettings['lp_existing_setting1'] = 'existing_value1';
    Config::$modSettings['lp_existing_setting2'] = 'existing_value2';

    $values = [
        // Mixed scenarios
        'lp_test_setting'      => 'test_value',      // New setting
        'lp_another_setting'   => 'another_value',   // New setting
        'lp_existing_setting1' => 'existing_value1', // Existing setting (should not be added)
        'lp_existing_setting2' => 'existing_value2', // Existing setting (should not be added)
        'lp_new_setting1'      => 'new_value1',      // New setting
        'lp_new_setting2'      => 'new_value2',      // New setting
        'lp_empty_setting'     => '',                // Empty value (should not be added)
        'lp_null_setting'      => null,              // Null value (should not be added)
        'lp_valid_setting'     => 'valid_value',     // New setting
    ];

    $reflection->callMethod('addDefaultValues', [$values]);

    expect(Config::$modSettings)->toHaveKey('lp_existing_setting1')
        ->and(Config::$modSettings['lp_existing_setting1'])->toBe('existing_value1')
        ->and(Config::$modSettings)->toHaveKey('lp_existing_setting2')
        ->and(Config::$modSettings['lp_existing_setting2'])->toBe('existing_value2')
        ->and(Config::$modSettings)->toHaveKey('lp_test_setting')
        ->and(Config::$modSettings['lp_test_setting'])->toBe('test_value')
        ->and(Config::$modSettings)->toHaveKey('lp_another_setting')
        ->and(Config::$modSettings['lp_another_setting'])->toBe('another_value')
        ->and(Config::$modSettings)->toHaveKey('lp_new_setting1')
        ->and(Config::$modSettings['lp_new_setting1'])->toBe('new_value1')
        ->and(Config::$modSettings)->toHaveKey('lp_new_setting2')
        ->and(Config::$modSettings['lp_new_setting2'])->toBe('new_value2')
        ->and(Config::$modSettings)->toHaveKey('lp_valid_setting')
        ->and(Config::$modSettings['lp_valid_setting'])->toBe('valid_value')
        ->and(Config::$modSettings)->not->toHaveKey('lp_empty_setting')
        ->and(Config::$modSettings)->not->toHaveKey('lp_null_setting');
});

it('prepares config fields for different field types', function () {
    $reflection = new ReflectionAccessor($this->config);

    // Set up mod settings for testing
    Config::$modSettings['lp_test_check'] = true;
    Config::$modSettings['lp_test_int'] = 42;
    Config::$modSettings['lp_test_text'] = 'sample text';
    Config::$modSettings['lp_test_select'] = 1;

    Utils::$context['config_vars'] = [
        [
            'name'  => 'lp_test_check',
            'label' => 'Test Check',
            'type'  => 'check',
            'data'  => [],
            'tab'   => 'basic',
        ],
        [
            'name'  => 'lp_test_int',
            'label' => 'Test Int',
            'type'  => 'int',
            'data'  => [],
            'tab'   => 'basic',
        ],
        [
            'name'  => 'lp_test_text',
            'label' => 'Test Text',
            'type'  => 'text',
            'data'  => [],
            'tab'   => 'basic',
            'placeholder' => 'Enter text',
        ],
        [
            'name'  => 'lp_test_select',
            'label' => 'Test Select',
            'type'  => 'select',
            'data'  => [[0, 'No'], [1, 'Yes']],
            'tab'   => 'basic',
            'attributes' => ['class' => 'select'],
        ],
    ];

    // This should not throw an exception and should process all field types
    $reflection->callMethod('prepareConfigFields');

    $fields = Utils::$context['posting_fields'];

    expect($fields['lp_test_check']['input']['attributes']['checked'])->toBeTrue()
        ->and($fields['lp_test_int']['input']['attributes']['value'])->toBe(42)
        ->and($fields['lp_test_text']['input']['attributes']['value'])->toBe('sample text')
        ->and($fields['lp_test_text']['input']['attributes']['placeholder'])->toBe('Enter text')
        ->and($fields['lp_test_select']['input']['tab'])->toBe('basic')
        ->and($fields['lp_test_select']['input']['options'])->toBe([
            'No'  => ['value' => 0, 'selected' => false],
            'Yes' => ['value' => 1, 'selected' => true],
        ]);
});

it('prepares config fields for callback and permissions field types', function () {
    $reflection = new ReflectionAccessor($this->config);

    Utils::$context['config_vars'] = [
        [
            'name'     => 'lp_test_callback',
            'label'    => 'Test Callback',
            'type'     => 'callback',
            'tab'      => 'basic',
            'callback' => function() { return 'callback result'; },
        ],
        [
            'name' => 'lp_test_permissions',
            'label' => 'Test Permissions',
            'type' => 'permissions',
            'tab'  => 'basic',
        ],
    ];

    // This should not throw an exception and should handle special field types
    $reflection->callMethod('prepareConfigFields');

    expect(true)->toBeTrue();
});

it('handles empty config vars array', function () {
    $reflection = new ReflectionAccessor($this->config);

    Utils::$context['config_vars'] = [];
    Utils::$context['posting_fields'] = [];

    // This should not throw an exception even with empty config vars
    $reflection->callMethod('prepareConfigFields');

    expect(true)->toBeTrue();
});

it('uses select options from the prepared context', function () {
    $reflection = new ReflectionAccessor($this->config);

    Config::$modSettings['lp_test_select'] = 'second';
    Utils::$context['config_vars'] = [
        'lp_test_select' => [
            'name'    => 'lp_test_select',
            'label'   => 'Test Select',
            'type'    => 'select',
            'tab'     => 'basic',
            'options' => ['first' => 'First', 'second' => 'Second'],
            'data'    => [['stale', 'Stale']],
        ],
    ];

    $reflection->callMethod('prepareConfigFields');

    expect(Utils::$context['posting_fields']['lp_test_select']['input']['options'])->toBe([
        'First'  => ['value' => 'first', 'selected' => false],
        'Second' => ['value' => 'second', 'selected' => true],
    ]);
});

it('prepares Bricks settings while preserving SMF dividers', function () {
    Config::$modSettings['lp_test_select'] = '0';
    $configVars = ConfigBuilder::make()->addVars([
        CheckConfig::make('lp_test_check')->setTab('basic'),
        DividerConfig::make(),
        SelectConfig::make('lp_test_select')->setTab('basic')->setOptions([0 => 'None', 2 => 'Two']),
    ])->build();
    $originalVars = $configVars;

    prepareDBSettingContext($configVars);

    expect($configVars)->toBe($originalVars)
        ->and(Utils::$context['config_vars'][0])->toBe('')
        ->and(Utils::$context['config_vars']['lp_test_check']['data'])->toBe([])
        ->and(Utils::$context['config_vars']['lp_test_select']['data'])->toBe([[0, 'None'], [2, 'Two']]);

    (new ReflectionAccessor($this->config))->callMethod('prepareConfigFields');

    expect(array_keys(Utils::$context['posting_fields']))->toBe(['lp_test_check', 'lp_test_select'])
        ->and(Utils::$context['posting_fields']['lp_test_select']['input']['options'])->toBe([
            'None' => ['value' => 0, 'selected' => true],
            'Two'  => ['value' => 2, 'selected' => false],
        ]);
});

it('handles unknown field type with default null', function () {
    $reflection = new ReflectionAccessor($this->config);

    $var = [
        'name'  => 'lp_unknown_field',
        'label' => 'Unknown Field',
        'type'  => 'unknown',
        'tab'   => 'basic',
    ];

    $data = [];
    $value = null;
    $defaultValue = null;

    $reflection->callMethod(
        'createFieldByType',
        ['unknown', 'lp_unknown_field', $var, $data, $value, $defaultValue]
    );

    expect(true)->toBeTrue();
});

it('creates callback field using VarFactory createTemplateCallback', function () {
    function template_callback_lp_test_template(): void
    {
        echo 'template output';
    }

    $reflection = new ReflectionAccessor($this->config);

    /* @uses template_callback_lp_test_template */
    Utils::$context['config_vars'] = [
        [
            'name'  => 'lp_test_template',
            'label' => 'Test Template',
            'type'  => 'callback',
            'tab'   => 'basic',
        ],
    ];

    $reflection->callMethod('prepareConfigFields');

    expect(true)->toBeTrue();

    Utils::$context['config_vars'][0]['name'] = 'lp_unknown_template';

    $reflection->callMethod('prepareConfigFields');

    expect(true)->toBeTrue();
});
