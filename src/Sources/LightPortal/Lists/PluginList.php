<?php declare(strict_types=1);

/**
 * TagList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Lists;

use const LP_ADDON_DIR;

if (! defined('SMF'))
	die('No direct access...');

class PluginList implements ListInterface
{
	public function __invoke(): array
	{
		if (empty($dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(static fn($item): string => basename($item), $dirs);
	}
}
