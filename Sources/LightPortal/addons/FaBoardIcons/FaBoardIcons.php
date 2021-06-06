<?php

namespace Bugo\LightPortal\Addons\FaBoardIcons;

/**
 * FaBoardIcons
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class FaBoardIcons
{
	/**
	 * @var string
	 */
	public $addon_type = 'article';

	/**
	 * Select columns with icon and color
	 *
	 * Выбираем столбцы с иконкой и цветом
	 *
	 * @param array $custom_columns
	 * @return void
	 */
	public function frontBoards(&$custom_columns)
	{
		global $sourcedir;

		if (!is_file($sourcedir . '/FA-BoardIcons/FA-BoardIcons.php'))
			return;

		$custom_columns[] = 'b.fabi_icon, b.fabi_color';
	}

	/**
	 * Change some result data
	 *
	 * Меняем некоторые результаты выборки
	 *
	 * @param array $boards
	 * @param array $row
	 * @return void
	 */
	public function frontBoardsOutput(&$boards, $row)
	{
		global $sourcedir, $modSettings;

		if (!is_file($sourcedir . '/FA-BoardIcons/FA-BoardIcons.php'))
			return;

		$icon = !empty($row['fabi_icon']) && empty($modSettings['fabi_force_default_icon']) ? $row['fabi_icon'] : (!empty($modSettings['fabi_default_icon']) ? $modSettings['fabi_default_icon'] : 'fas fa-comments');
		$color = !empty($row['fabi_color']) && empty($modSettings['fabi_force_default_color']) ? $row['fabi_color'] : (!empty($modSettings['fabi_default_color']) ? $modSettings['fabi_default_color'] : '');

		$boards[$row['id_board']]['name'] = '<i class="' . $icon . ' fa"' . (!empty($color) ? ' style="color: ' . $color . '"' : '') . '></i> ' . $boards[$row['id_board']]['name'];
	}
}
