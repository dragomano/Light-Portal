<?php

/**
 * TinyPortal.php
 *
 * @package TinyPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Addons\Plugin;

class TinyPortal extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'impex';

	/**
	 * Add "Import from TinyPortal" item to the admin menu
	 *
	 * Добавляем в меню админки пункт «Импорт из TinyPortal»
	 *
	 * @param array $admin_areas
	 * @return void
	 */
	public function addAdminAreas(array &$admin_areas)
	{
		global $user_info, $txt;

		if ($user_info['is_admin']) {
			$admin_areas['lp_portal']['areas']['lp_blocks']['subsections']['import_from_tp'] = array($txt['lp_tiny_portal']['label_name']);
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_tp']  = array($txt['lp_tiny_portal']['label_name']);
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
	public function addBlockAreas(array &$subActions)
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
	public function addPageAreas(array &$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_tp'] = array(new PageImport, 'main');
	}
}
