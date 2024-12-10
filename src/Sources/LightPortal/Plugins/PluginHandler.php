<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

use function array_map;
use function basename;
use function class_exists;
use function glob;

use const DIRECTORY_SEPARATOR;
use const GLOB_ONLYDIR;

if (! defined('LP_NAME'))
	die('No direct access...');

final class PluginHandler
{
	private readonly PluginRegistry $registry;

	private readonly ConfigHandler $configHandler;

	private readonly LangHandler $langHandler;

	private readonly AssetHandler $assetHandler;

	private static EventManager $manager;

	public function __construct(array $plugins = [])
	{
		$this->registry = PluginRegistry::getInstance();
		$this->configHandler = new ConfigHandler();
		$this->langHandler = new LangHandler();
		$this->assetHandler = new AssetHandler();

		if (empty(self::$manager)) {
			self::$manager = new EventManager();
		}

		$this->prepareListeners($plugins);
		$this->prepareAssets();
		$this->assetHandler->minify();

		Utils::$context['lp_loaded_addons'] = $this->registry->getAll();
	}

	public function getManager(): EventManager
	{
		return self::$manager;
	}

	public function getRegistry(): PluginRegistry
	{
		return $this->registry;
	}

	public function getAll(): array
	{
		if (empty($dirs = glob(__DIR__ . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(static fn($item): string => basename($item), $dirs);
	}

	private function prepareAssets(): void
	{
		$assets = [];

		self::$manager->dispatch(
			PortalHook::prepareAssets,
			new Event(new class ($assets) {
				public function __construct(public array &$assets) {}
			})
		);

		$this->assetHandler->prepare($assets);
	}

	private function prepareListeners(array $plugins = []): void
	{
		$plugins = $plugins ?: Setting::getEnabledPlugins();

		if ($plugins === [] || isset(Utils::$context['uninstalling']))
			return;

		foreach ($plugins as $pluginName) {
			$className = __NAMESPACE__ . "\\$pluginName\\$pluginName";

			if (! class_exists($className))
				continue;

			$snakeName = Str::getSnakeName($pluginName);

			if (! $this->registry->has($snakeName)) {
				$path = __DIR__ . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR;

				$this->configHandler->handle($snakeName);
				$this->langHandler->handle($path, $snakeName);
				$this->assetHandler->handle($path, $pluginName);

				/** @var Plugin $className */
				$class = new $className();

				self::$manager->addListeners(PortalHook::cases(), $class);

				$this->registry->add($snakeName, [
					'name'     => $pluginName,
					'icon'     => $class->icon,
					'type'     => $class->type,
					'saveable' => $class->saveable,
				]);
			}
		}
	}
}
