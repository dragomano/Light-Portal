<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Plugins\PluginHandler;

final class EventManagerFactory
{
	private static array $plugins = [];

	private static PluginHandler $handler;

	private static EventManager $manager;

	public function __invoke(array $plugins = []): EventManager
	{
		if ($plugins !== self::$plugins) {
			self::$plugins = $plugins;
			self::$handler = new PluginHandler($plugins);
		}

		self::$handler ??= new PluginHandler();

		self::$manager = self::$handler->getManager();

		return self::$manager;
	}
}
