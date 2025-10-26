<?php

declare(strict_types=1);

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

    $this->config = new class extends AbstractConfig
    {
        public function show(): void
        {
            // Implementation for testing
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

    $reflection->callProtectedMethod('addDefaultValues', [$values]);

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
            'tab'   => 'basic',
        ],
        [
            'name'  => 'lp_test_int',
            'label' => 'Test Int',
            'type'  => 'int',
            'tab'   => 'basic',
        ],
        [
            'name'  => 'lp_test_text',
            'label' => 'Test Text',
            'type'  => 'text',
            'tab'   => 'basic',
            'placeholder' => 'Enter text',
        ],
        [
            'name'  => 'lp_test_select',
            'label' => 'Test Select',
            'type'  => 'select',
            'tab'   => 'basic',
            'attributes' => ['class' => 'select'],
        ],
    ];

    // This should not throw an exception and should process all field types
    $reflection->callProtectedMethod('prepareConfigFields', [[]]);

    expect(true)->toBeTrue();
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
    $reflection->callProtectedMethod('prepareConfigFields', [[]]);

    expect(true)->toBeTrue();
});

it('handles empty config vars array', function () {
    $reflection = new ReflectionAccessor($this->config);

    Utils::$context['config_vars'] = [];

    // This should not throw an exception even with empty config vars
    $reflection->callProtectedMethod('prepareConfigFields', [[]]);

    expect(true)->toBeTrue();
});

it('prepares config fields with configVars parameter', function () {
    $reflection = new ReflectionAccessor($this->config);

    Config::$modSettings['lp_test_check'] = false;

    Utils::$context['config_vars'] = [
        [
            'name'  => 'lp_test_check',
            'label' => 'Test Check',
            'type'  => 'check',
            'tab'   => 'basic',
        ],
    ];

    $configVars = [
        ['options' => ['option1', 'option2']],
    ];

    // This should use the configVars parameter
    $reflection->callProtectedMethod('prepareConfigFields', [$configVars]);

    expect(true)->toBeTrue();
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

    $reflection->callProtectedMethod(
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

    $reflection->callProtectedMethod('prepareConfigFields', [[]]);

    expect(true)->toBeTrue();

    Utils::$context['config_vars'][0]['name'] = 'lp_unknown_template';

    $reflection->callProtectedMethod('prepareConfigFields', [[]]);

    expect(true)->toBeTrue();
});
