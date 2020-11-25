<?php

namespace Bugo\LightPortal\Addons\EhPortal;

/**
 * EhPortal
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class EhPortal
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public static $addon_type = 'impex';

	/**
	 * Add hooks for the admin panel
	 *
	 * Добавляем хуки для связи с админкой
	 *
	 * @return void
	 */
	public static function init()
	{
		add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas', false, __FILE__);
	}

	/**
	 * Add "Import from EhPortal" item to the admin menu
	 *
	 * Добавляем в меню админки пункт «Импорт из EhPortal»
	 *
	 * @param array $admin_areas
	 * @return void
	 */
	public static function adminAreas(array &$admin_areas)
	{
		global $txt;

		$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_ep'] = array($txt['lp_eh_portal_addon_label_name']);
	}

	/**
	 * Add "Import from EhPortal" tab
	 *
	 * Добавляем вкладку «Импорт из EhPortal»
	 *
	 * @param array $subActions
	 * @return void
	 */
	public static function addPageAreas(&$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_ep'] = array(Import::class, 'main');
	}
}
