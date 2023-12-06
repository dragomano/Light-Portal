<?php

/**
 * EzPortal.php
 *
 * @package EzPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\EzPortal;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class EzPortal extends Plugin
{
	public string $type = 'impex';

	public function addAdminAreas(array &$admin_areas): void
	{
		if ($this->user_info['is_admin'])
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_ez'] = [$this->context['lp_icon_set']['import'] . $this->txt['lp_ez_portal']['label_name']];
	}

	public function addPageAreas(array &$subActions): void
	{
		if ($this->user_info['is_admin'])
			$subActions['import_from_ez'] = [new Import, 'main'];
	}

	public function importPages(array &$items, array &$titles): void
	{
		if ($this->request('sa') !== 'import_from_ez')
			return;

		foreach ($items as $page_id => $item) {
			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $this->language,
				'title'   => $item['subject']
			];

			if ($this->language !== 'english' && ! empty($this->modSettings['userLanguage'])) {
				$titles[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'lang'    => 'english',
					'title'   => $item['subject']
				];
			}

			unset($items[$page_id]['subject']);
		}
	}
}
