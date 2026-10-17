<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Assets\ExternalResource;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Repositories\PluginRepositoryInterface;
use Tests\AppMockRegistry;

function lpInitPluginContext(string $snake): void
{
    Lang::$txt['lp_' . $snake] = Lang::$txt['lp_' . $snake] ?? [];
    Utils::$context['lp_' . $snake . '_plugin'] = Utils::$context['lp_' . $snake . '_plugin'] ?? [];
}

beforeEach(function () {
    foreach ([
        'test_configured_plugin',
        'test_plain_plugin',
        'test_multi_type_plugin',
        'test_parent_plugin',
        'test_child_plugin',
    ] as $snake) {
        lpInitPluginContext($snake);
    }
});

it('resolves camel and snake names', function () {
    $plugin = new TestConfiguredPlugin();

    expect($plugin->getCamelName())->toBe('TestConfiguredPlugin')
        ->and($plugin->getSnakeName())->toBe('test_configured_plugin')
        ->and((string) $plugin)->toBe('TestConfiguredPlugin');
});

it('applies default attribute values for plugins without attribute', function () {
    $plugin = new TestPlainPlugin();

    expect($plugin->type)->toBe(PluginType::OTHER->value)
        ->and($plugin->icon)->toBe('fas fa-puzzle-piece')
        ->and($plugin->showSaveButton)->toBeTrue()
        ->and($plugin->showContentClass)->toBeTrue();
});

it('applies attribute values from the class', function () {
    $plugin = new TestConfiguredPlugin();

    expect($plugin->type)->toBe(PluginType::BLOCK->value)
        ->and($plugin->icon)->toBe('fas fa-test')
        ->and($plugin->showSaveButton)->toBeFalse()
        ->and($plugin->showContentClass)->toBeFalse();
});

it('joins multiple attribute types', function () {
    $plugin = new TestMultiTypePlugin();

    expect($plugin->type)->toBe('block games');
});

it('inherits attribute values from parent class', function () {
    $plugin = new TestChildPlugin();

    expect($plugin->type)->toBe(PluginType::BLOCK->value)
        ->and($plugin->icon)->toBe('fas fa-parent');
});

it('checks if the plugin is enabled', function () {
    Config::$modSettings['lp_enabled_plugins'] = 'TestConfiguredPlugin';

    expect((new TestConfiguredPlugin())->isEnabled())->toBeTrue();

    Config::$modSettings['lp_enabled_plugins'] = 'AnotherPlugin';

    expect((new TestConfiguredPlugin())->isEnabled())->toBeFalse();
});

it('adds default values to the plugin context', function () {
    $repository = mock(PluginRepositoryInterface::class);
    $repository->shouldReceive('changeSettings')
        ->once()
        ->with('test_configured_plugin', ['lp_test_param' => 'default']);

    AppMockRegistry::set(PluginRepositoryInterface::class, $repository);

    $plugin = new TestConfiguredPlugin();
    $plugin->addDefaultValues(['lp_test_param' => 'default']);

    expect(Utils::$context['lp_test_configured_plugin_plugin']['lp_test_param'])->toBe('default');

    // Existing context values must not be overwritten
    $plugin->addDefaultValues(['lp_test_param' => 'other']);

    expect(Utils::$context['lp_test_configured_plugin_plugin']['lp_test_param'])->toBe('default');
});

it('loads external resources', function () {
    $plugin = new TestPlainPlugin();

    $resource1 = mock(ExternalResource::class);
    $resource2 = mock(ExternalResource::class);
    $resource1->shouldReceive('load')->once();
    $resource2->shouldReceive('load')->once();

    $plugin->loadExternalResources($resource1, $resource2);
});

#[PluginAttribute(type: PluginType::BLOCK, icon: 'fas fa-test', showSaveButton: false, showContentClass: false)]
class TestConfiguredPlugin extends Plugin
{
}

class TestPlainPlugin extends Plugin
{
}

#[PluginAttribute(type: [PluginType::BLOCK, PluginType::GAMES])]
class TestMultiTypePlugin extends Plugin
{
}

#[PluginAttribute(type: PluginType::BLOCK, icon: 'fas fa-parent')]
class TestParentPlugin extends Plugin
{
}

class TestChildPlugin extends TestParentPlugin
{
}
