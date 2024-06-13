<?php

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.04.24
 */

namespace Bugo\LightPortal\Addons\EhPortalMigration;

use Bugo\Compat\{Config, Lang, User};
use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\{Icon, Language};

if (! defined('LP_NAME'))
	die('No direct access...');

class EhPortalMigration extends Plugin
{
	public string $type = 'impex';

	public function updateAdminAreas(array &$areas): void
	{
		if (User::$info['is_admin']) {
			$areas['lp_blocks']['subsections']['import_from_ep'] = [
				Icon::get('import') . Lang::$txt['lp_eh_portal_migration']['label_name']
			];

			$areas['lp_pages']['subsections']['import_from_ep'] = [
				Icon::get('import') . Lang::$txt['lp_eh_portal_migration']['label_name']
			];

			$areas['lp_categories']['subsections']['import_from_ep'] = [
				Icon::get('import') . Lang::$txt['lp_eh_portal_migration']['label_name']
			];
		}
	}

	public function updateBlockAreas(array &$areas): void
	{
		$areas['import_from_ep'] = [new BlockImport, 'main'];
	}

	public function updatePageAreas(array &$areas): void
	{
		$areas['import_from_ep'] = [new PageImport(), 'main'];
	}

	public function updateCategoryAreas(array &$areas): void
	{
		$areas['import_from_ep'] = [new CategoryImport(), 'main'];
	}

	public function importPages(array &$items, array &$titles): void
	{
		if ($this->request('sa') !== 'import_from_ep')
			return;

		foreach ($items as $pageId => $item) {
			$titles[] = [
				'item_id' => $pageId,
				'type'    => 'page',
				'lang'    => Config::$language,
				'title'   => $item['title'],
			];

			if (Config::$language !== Language::getFallbackValue() && ! empty(Config::$modSettings['userLanguage'])) {
				$titles[] = [
					'item_id' => $pageId,
					'type'    => 'page',
					'lang'    => Language::getFallbackValue(),
					'title'   => $item['title'],
				];
			}

			unset($items[$pageId]['title']);
		}
	}
}
