<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\EventManager;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Throwable;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

final readonly class PluginHandler
{
	private AssetHandler $assetHandler;

	private ConfigHandler $configHandler;

	private LangHandler $langHandler;

	private EventManager $manager;

	public function __construct(array $plugins = [])
	{
		$this->manager       = app(EventManager::class);
		$this->assetHandler  = app(AssetHandler::class);
		$this->configHandler = app(ConfigHandler::class);
		$this->langHandler   = app(LangHandler::class);

		$this->prepareListeners($plugins);
		$this->prepareAssets();
		$this->assetHandler->minify();

		Utils::$context['lp_loaded_addons'] = $this->getLoadedPlugins();
	}

	public function getLoadedPlugins(): array
	{
		if (! app()->has('plugins')) {
			return [];
		}

		$warehouse = [];

		try {
			$warehouse = app()->get('plugins');
		} catch (Throwable $e) {
			ErrorHandler::log('[LP] pluginHandler: ' . $e->getMessage(), file: $e->getFile(), line: $e->getLine());
		}

		$plugins = array_map(function (PluginInterface $plugin) {
			$data = get_object_vars($plugin);
			$data['name']     = $plugin->getCamelName();
			$data['type']     = $plugin->getPluginType();
			$data['icon']     = $plugin->getPluginIcon();
			$data['saveable'] = $plugin->isPluginSaveable();

			return [$plugin->getSnakeName() => $data];
		}, $warehouse);

		return array_merge(...$plugins);
	}

	public function getManager(): EventManager
	{
		return $this->manager;
	}

	private function prepareAssets(): void
	{
		$assets = [];

		$this->manager->dispatch(PortalHook::prepareAssets, ['assets' => &$assets]);

		$this->assetHandler->prepare($assets);
	}

	private function prepareListeners(array $plugins = []): void
	{
		$plugins = $plugins ?: Setting::getEnabledPlugins();

		if ($plugins === [] || isset(Utils::$context['uninstalling']))
			return;

		foreach ($plugins as $pluginName) {
			$className = __NAMESPACE__ . "\\$pluginName\\$pluginName";

			if (! class_exists($className) || app()->has($className))
				continue;

			$this->handlePlugin($pluginName);

			app()->add($className)->addTag('plugins');

			$this->manager->addHookListener(app($className));
		}
	}

	private function handlePlugin(string $pluginName): void
	{
		$snakeName = Str::getSnakeName($pluginName);

		$path = __DIR__ . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR;

		$this->assetHandler->handle($path, $pluginName);
		$this->configHandler->handle($snakeName);
		$this->langHandler->handle($path, $snakeName);
	}
}
