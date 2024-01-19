<?php

/**
 * FaBoardIcons.php
 *
 * @package FaBoardIcons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\FaBoardIcons;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\Config;

if (! defined('LP_NAME'))
	die('No direct access...');

class FaBoardIcons extends Plugin
{
	public string $type = 'article';

	public function frontBoards(array &$custom_columns): void
	{
		if (! $this->isBaseModInstalled())
			return;

		$custom_columns[] = 'b.fabi_icon, b.fabi_color';
	}

	public function frontBoardsOutput(array &$boards, array $row): void
	{
		if (! $this->isBaseModInstalled())
			return;

		$icon = ! empty($row['fabi_icon']) && empty(Config::$modSettings['fabi_force_default_icon']) ? $row['fabi_icon'] : (empty(Config::$modSettings['fabi_default_icon']) ? 'fas fa-comments' : Config::$modSettings['fabi_default_icon']);
		$color = ! empty($row['fabi_color']) && empty(Config::$modSettings['fabi_force_default_color']) ? $row['fabi_color'] : (empty(Config::$modSettings['fabi_default_color']) ? '' : Config::$modSettings['fabi_default_color']);

		$boards[$row['id_board']]['title'] = '<i class="' . $icon . ' fa"' . (empty($color) ? '' : ' style="color: ' . $color . '"') . '></i> ' . $boards[$row['id_board']]['title'];
	}

	private function isBaseModInstalled(): bool
	{
		return is_file(Config::$sourcedir . '/FA-BoardIcons/FA-BoardIcons.php');
	}
}
