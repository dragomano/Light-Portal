<?php

/**
 * EhPortal.php
 *
 * @package EhPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 10.12.23
 */

namespace Bugo\LightPortal\Addons\EhPortal;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class EhPortal extends Plugin
{
	public string $type = 'impex';

	public function updateAdminAreas(array &$areas): void
	{
		if ($this->user_info['is_admin'])
			$areas['lp_pages']['subsections']['import_from_ep'] = [$this->context['lp_icon_set']['import'] . $this->txt['lp_eh_portal']['label_name']];
	}

	public function updatePageAreas(array &$areas): void
	{
		if ($this->user_info['is_admin'])
			$areas['import_from_ep'] = [new Import, 'main'];
	}

	public function importPages(array &$items, array &$titles): void
	{
		if ($this->request('sa') !== 'import_from_ep')
			return;

		foreach ($items as $page_id => $item) {
			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $this->language,
				'title'   => $item['title']
			];

			if ($this->language !== 'english' && ! empty($this->modSettings['userLanguage'])) {
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
