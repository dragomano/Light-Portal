<?php

namespace Bugo\LightPortal\Addons\TinyPortal;

/**
 * TinyPortal
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

class TinyPortal
{
	/**
	 * @var string
	 */
	public $addon_type = 'impex';

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

		if ($user_info['is_admin']) {
			$admin_areas['lp_portal']['areas']['lp_blocks']['subsections']['import_from_tp'] = array($txt['lp_tiny_portal_addon_label_name']);
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_tp']  = array($txt['lp_tiny_portal_addon_label_name']);
		}
	}

	/**
	 * Add "Import from TinyPortal" tab for Blocks area
	 *
	 * Добавляем вкладку «Импорт из TinyPortal»
	 *
	 * @param array $subActions
	 * @return void
	 */
	public function addBlockAreas(&$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_tp'] = array(new BlockImport, 'main');
	}

	/**
	 * Add "Import from TinyPortal" tab for Pages area
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
			$subActions['import_from_tp'] = array(new PageImport, 'main');
	}
}
