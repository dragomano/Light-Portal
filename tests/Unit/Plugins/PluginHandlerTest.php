<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Assets\AssetHandler;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventManager;
use LightPortal\Plugins\ConfigHandler;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\LangHandler;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginHandler;
use Tests\AppMockRegistry;

class TestPluginHandlerContainer
{
    public array $registered = [];

    public function __construct(private array $services = []) {}

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function get(string $id): mixed
    {
        return $this->services[$id] ?? null;
    }

    public function add(string $id): static
    {
        $this->registered[] = $id;

        return $this;
    }

    public function addTag(string $tag): static
    {
        return $this;
    }
}

class TestHandlerPlugin extends Plugin
{
    public function prepareAssets(Event $e): void
    {
        $GLOBALS['prepare_assets_calls'] = ($GLOBALS['prepare_assets_calls'] ?? 0) + 1;

        $e->args->builder->scripts()->add('script.js');
    }
}

beforeEach(function () {
    $GLOBALS['prepare_assets_calls'] = 0;

    Lang::$txt['lp_test_handler_plugin'] = [];
    Utils::$context['lp_test_handler_plugin_plugin'] = [];

    Config::$boardurl = 'https://example.com';

    $this->plugin = new TestHandlerPlugin();
    $this->pluginClass = TestHandlerPlugin::class;

    $this->manager = mock(EventManager::class);
    $this->assetHandler = mock(AssetHandler::class);
    $this->configHandler = mock(ConfigHandler::class);
    $this->langHandler = mock(LangHandler::class);

    $this->assetHandler->shouldReceive('minify')->byDefault();
    $this->assetHandler->shouldReceive('prepare')->byDefault();

    $this->manager->shouldReceive('getAll')->andReturn([])->byDefault();
});

afterEach(function () {
    unset($GLOBALS['prepare_assets_calls']);
});

it('does nothing when no plugins are enabled', function () {
    Config::$modSettings['lp_enabled_plugins'] = '';

    $container = new TestPluginHandlerContainer();
    AppMockRegistry::set('', $container);

    $this->assetHandler->shouldNotReceive('handle');
    $this->configHandler->shouldNotReceive('handle');
    $this->langHandler->shouldNotReceive('handle');

    $handler = new PluginHandler($this->manager, $this->assetHandler, $this->configHandler, $this->langHandler);

    expect($handler->getLoadedPlugins())->toBe([])
        ->and(Utils::$context['lp_loaded_addons'])->toBe([])
        ->and($container->registered)->toBeEmpty();
});

it('loads enabled plugins and their assets', function () {
    Config::$modSettings['lp_enabled_plugins'] = 'HelloPortal';

    $listenerClass = 'LightPortal\Plugins\HelloPortal\HelloPortal';

    $expectedAssets = [
        'scripts' => ['test_handler_plugin' => [
            'https://example.com/Sources/LightPortal/Plugins/TestHandlerPlugin/script.js',
        ]],
    ];

    $this->assetHandler->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(
            fn($path) => str_ends_with($path, 'Plugins' . DIRECTORY_SEPARATOR . 'HelloPortal' . DIRECTORY_SEPARATOR)
        ), 'HelloPortal');
    $this->configHandler->shouldReceive('handle')->once()->with('hello_portal');
    $this->langHandler->shouldReceive('handle')->once();

    $this->assetHandler->shouldReceive('prepare')
        ->once()
        ->with(Mockery::on(fn($assets) => $assets['scripts'] === $expectedAssets['scripts']));

    $this->manager->shouldReceive('getAll')
        ->with(PortalHook::prepareAssets->name)
        ->andReturn([$this->plugin]);

    $this->manager->shouldReceive('addHookListener')->once()->with($this->plugin);

    $container = new TestPluginHandlerContainer([
        'plugins' => [$this->plugin],
    ]);
    AppMockRegistry::set('', $container);
    AppMockRegistry::set($listenerClass, $this->plugin);

    $handler = new PluginHandler($this->manager, $this->assetHandler, $this->configHandler, $this->langHandler);

    expect($container->registered)->toBe([$listenerClass])
        ->and($GLOBALS['prepare_assets_calls'])->toBe(1)
        ->and(Utils::$context['lp_loaded_addons'])->toHaveKey('test_handler_plugin')
        ->and($handler->getLoadedPlugins()['test_handler_plugin']['name'])->toBe('TestHandlerPlugin')
        ->and($handler->getManager())->toBe($this->manager);
});

it('skips already registered plugins', function () {
    Config::$modSettings['lp_enabled_plugins'] = 'HelloPortal';

    $listenerClass = 'LightPortal\Plugins\HelloPortal\HelloPortal';

    $container = new TestPluginHandlerContainer([
        $listenerClass => $this->plugin,
    ]);
    AppMockRegistry::set('', $container);

    $this->assetHandler->shouldNotReceive('handle');
    $this->configHandler->shouldNotReceive('handle');
    $this->langHandler->shouldNotReceive('handle');
    $this->manager->shouldNotReceive('addHookListener');

    new PluginHandler($this->manager, $this->assetHandler, $this->configHandler, $this->langHandler);

    expect($container->registered)->toBeEmpty()
        ->and($GLOBALS['prepare_assets_calls'])->toBe(0);
});
