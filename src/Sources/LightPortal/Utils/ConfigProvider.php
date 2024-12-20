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

namespace Bugo\LightPortal\Utils;

use function dirname;
use function is_file;

class ConfigProvider
{
	public function get(): array
	{
		$configFile = is_file(dirname(__DIR__) . '/development.config.php')
			? '/development.config.php'
			: '/production.config.php';

		return require dirname(__DIR__) . $configFile;
	}
}
