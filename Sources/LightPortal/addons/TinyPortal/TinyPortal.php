<?php

namespace Bugo\LightPortal\Addons\TinyPortal;

/**
 * TinyPortal
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

class TinyPortal
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public $addon_type = 'impex';

	/**
	 * Add hooks for the admin panel
	 *
	 * Добавляем хуки для связи с админкой
	 *
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas#', false, __FILE__);
	}

	/**
	 * Add "Import from TinyPortal" item to the admin menu
	 *
	 * Добавляем в меню админки пункт «Импорт из TinyPortal»
	 *
	 * @param array $admin_areas
	 * @return void
	 */
	public function adminAreas(array &$admin_areas)
	{
		global $user_info, $txt;

		if ($user_info['is_admin'])
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_tp'] = array($txt['lp_tiny_portal_addon_label_name']);
	}

	/**
	 * Add "Import from TinyPortal" tab
	 *
	 * Добавляем вкладку «Импорт из TinyPortal»
	 *
	 * @param array $subActions
	 * @return void
	 */
	public function addPageAreas(&$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_tp'] = array(new Import, 'main');
	}
}
