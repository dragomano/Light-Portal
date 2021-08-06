<?php

/**
 * EzPortal
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\EzPortal;

use Bugo\LightPortal\Addons\Plugin;

class EzPortal extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'impex';

	/**
	 * Add "Import from EzPortal" item to the admin menu
	 *
	 * Добавляем в меню админки пункт «Импорт из EzPortal»
	 *
	 * @param array $admin_areas
	 * @return void
	 */
	public function addAdminAreas(array &$admin_areas)
	{
		global $user_info, $txt;

		if ($user_info['is_admin'])
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_ez'] = array($txt['lp_ez_portal']['label_name']);
	}

	/**
	 * Add "Import from EzPortal" tab
	 *
	 * Добавляем вкладку «Импорт из EzPortal»
	 *
	 * @param array $subActions
	 * @return void
	 */
	public function addPageAreas(&$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_ez'] = array(new Import, 'main');
	}
}
