<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use LightPortal\Assets\AssetHandler;
use Tests\ReflectionAccessor;

beforeEach(function () {
    require_once __DIR__ . '/../../minify_polyfill.php';

    $this->themeDir = sys_get_temp_dir() . '/lp_asset_handler_' . uniqid();

    Theme::$current->settings['default_theme_dir'] = $this->themeDir;

    Config::$modSettings['lp_enabled_plugins'] = 'TestPlugin';

    $this->handler = new AssetHandler();
});

afterEach(function () {
    if (is_dir($this->themeDir)) {
        recursive_rmdir($this->themeDir);
    }
});

if (! function_exists('recursive_rmdir')) {
    function recursive_rmdir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $item) {
            is_dir($item) ? recursive_rmdir($item) : unlink($item);
        }

        @rmdir($dir);
    }
}

it('creates theme directories and downloads assets', function () {
    $assets = [
        'css' => [
            'test_plugin' => ['https://example.com/fake_style.css'],
        ],
    ];

    $this->handler->prepare($assets);

    $target = $this->themeDir . '/css/light_portal/test_plugin/fake_style.css';

    expect(is_dir($this->themeDir . '/css/light_portal/test_plugin'))->toBeTrue()
        ->and(is_file($target))->toBeTrue();
});

it('does not overwrite already downloaded assets', function () {
    $target = $this->themeDir . '/css/light_portal/test_plugin/fake_style.css';

    mkdir(dirname($target), 0755, true);
    file_put_contents($target, 'custom content');

    $this->handler->prepare([
        'css' => ['test_plugin' => ['https://example.com/fake_style.css']],
    ]);

    expect(file_get_contents($target))->toBe('custom content');
});

it('skips empty asset groups', function () {
    $this->handler->prepare(['css' => [], 'scripts' => [], 'images' => []]);

    expect(is_dir($this->themeDir . '/css/light_portal'))->toBeFalse();
});

it('handles existing plugin files for enabled plugins', function () {
    $pluginDir = sys_get_temp_dir() . '/lp_plugin_' . uniqid();
    mkdir($pluginDir);
    file_put_contents($pluginDir . '/style.css', 'body { color: red; }');
    file_put_contents($pluginDir . '/script.js', 'console.log(1);');

    $this->handler->handle($pluginDir . '/', 'TestPlugin');

    $reflection = new ReflectionAccessor($this->handler);

    expect($reflection->getProperty('maxCssFilemtime'))->toBeInt()
        ->and($reflection->getProperty('maxCssFilemtime'))->toBeGreaterThan(0)
        ->and($reflection->getProperty('maxJsFilemtime'))->toBeGreaterThan(0);

    recursive_rmdir($pluginDir);
});

it('tracks filemtime for disabled plugins as well', function () {
    Config::$modSettings['lp_enabled_plugins'] = 'OtherPlugin';

    $pluginDir = sys_get_temp_dir() . '/lp_plugin_' . uniqid();
    mkdir($pluginDir);
    file_put_contents($pluginDir . '/style.css', 'body { color: red; }');

    $this->handler->handle($pluginDir . '/', 'TestPlugin');

    $reflection = new ReflectionAccessor($this->handler);

    expect($reflection->getProperty('maxCssFilemtime'))->toBeGreaterThan(0);

    recursive_rmdir($pluginDir);
});

it('minifies collected files', function () {
    mkdir($this->themeDir . '/css/light_portal', 0755, true);
    mkdir($this->themeDir . '/scripts/light_portal', 0755, true);

    $this->handler->minify();

    expect(is_file($this->themeDir . '/css/light_portal/plugins.css'))->toBeTrue()
        ->and(is_file($this->themeDir . '/scripts/light_portal/plugins.js'))->toBeTrue();
});
