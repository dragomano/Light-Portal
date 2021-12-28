<?php

/**
 * EhPortal.php
 *
 * @package EhPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.12.21
 */

namespace Bugo\LightPortal\Addons\EhPortal;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class EhPortal extends Plugin
{
	public string $type = 'impex';

	public function addAdminAreas(array &$admin_areas)
	{
		global $user_info, $txt;

		if ($user_info['is_admin'])
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_ep'] = array($txt['lp_eh_portal']['label_name']);
	}

	public function addPageAreas(array &$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_ep'] = array(new Import, 'main');
	}

	public function importPages(array &$items, array &$titles)
	{
		global $language, $modSettings;

		if (Helper::request('sa') !== 'import_from_ep')
			return;

		foreach ($items as $page_id => $item) {
			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $language,
				'title'   => $item['title']
			];

			if ($language !== 'english' && ! empty($modSettings['userLanguage'])) {
				$titles[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'lang'    => 'english',
					'title'   => $item['title']
				];
			}

			unset($items[$page_id]['title']);
		}
	}
}
