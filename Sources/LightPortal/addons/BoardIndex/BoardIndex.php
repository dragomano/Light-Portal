<?php

namespace Bugo\LightPortal\Addons\BoardIndex;

/**
 * BoardIndex
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardIndex
{

	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public static $addon_type = 'other';

	/**
	 * Add settings
	 *
	 * Добавляем настройки
	 *
	 * @param array $config_vars
	 * @return void
	 */
	public static function addSettings(&$config_vars)
	{
		global $modSettings;

		if (!isset($modSettings['lp_board_index_addon_allow_for_spiders']))
			updateSettings(['lp_board_index_addon_allow_for_spiders' => false]);

		$config_vars[] = array('check', 'lp_board_index_addon_allow_for_spiders');
	}

	/**
	 * Add used hooks
	 *
	 * Добавляем используемые хуки
	 *
	 * @return void
	 */
	public static function init()
	{
		add_integration_function('integrate_mark_read_button', __CLASS__ . '::toggleRobotNoIndex', false, __FILE__);
	}

	/**
	 * Toggle indexing of the main forum page
	 *
	 * Переключаем возможность индексации главной страницы форума
	 *
	 * @return void
	 */
	public static function toggleRobotNoIndex()
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_frontpage_mode']))
			$context['robot_no_index'] = empty($modSettings['lp_board_index_addon_allow_for_spiders']);
	}
}
