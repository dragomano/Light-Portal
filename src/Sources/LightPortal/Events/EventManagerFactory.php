<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Events;

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
			self::$handler = app(PluginHandler::class)($plugins);
		}

		self::$handler ??= app(PluginHandler::class)();
		self::$manager = self::$handler->getManager();

		return self::$manager;
	}
}
