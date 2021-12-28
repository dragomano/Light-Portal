<?php

/**
 * FaBoardIcons.php
 *
 * @package FaBoardIcons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\FaBoardIcons;

use Bugo\LightPortal\Addons\Plugin;

class FaBoardIcons extends Plugin
{
	public string $type = 'article';

	public function frontBoards(array &$custom_columns)
	{
		if (! $this->isBaseModInstalled())
			return;

		$custom_columns[] = 'b.fabi_icon, b.fabi_color';
	}

	public function frontBoardsOutput(array &$boards, array $row)
	{
		global $modSettings;

		if (! $this->isBaseModInstalled())
			return;

		$icon = ! empty($row['fabi_icon']) && empty($modSettings['fabi_force_default_icon']) ? $row['fabi_icon'] : (empty($modSettings['fabi_default_icon']) ? 'fas fa-comments' : $modSettings['fabi_default_icon']);
		$color = ! empty($row['fabi_color']) && empty($modSettings['fabi_force_default_color']) ? $row['fabi_color'] : (empty($modSettings['fabi_default_color']) ? '' : $modSettings['fabi_default_color']);

		$boards[$row['id_board']]['title'] = '<i class="' . $icon . ' fa"' . (empty($color) ? '' : ' style="color: ' . $color . '"') . '></i> ' . $boards[$row['id_board']]['title'];
	}

	private function isBaseModInstalled(): bool
	{
		global $sourcedir;

		return is_file($sourcedir . '/FA-BoardIcons/FA-BoardIcons.php');
	}
}
