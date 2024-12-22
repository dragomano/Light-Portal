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

class ConfigHandler
{
	private static array $settings;

	public function handle(string $snakeName): void
	{
		self::$settings ??= app('plugin_repo')->getSettings();

		// @TODO These variables are still needed in some templates
		Utils::$context['lp_' . $snakeName . '_plugin'] = self::$settings[$snakeName] ?? [];
	}
}
