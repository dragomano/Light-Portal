<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\Config;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractConfig
{
	abstract public function show(): void;

	protected function addDefaultValues(array $values): void
	{
		$addSettings = [];

		foreach ($values as $key => $value) {
			if (empty($value)) continue;

			if (! isset(Config::$modSettings[$key])) {
				$addSettings[$key] = $value;
			}
		}

		Config::updateModSettings($addSettings);
	}
}
