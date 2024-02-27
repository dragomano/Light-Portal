<?php declare(strict_types=1);

/**
 * CategoryImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\{Config, Database as Db};
use Bugo\Compat\{ErrorHandler, Lang, Theme, Utils};

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class CategoryImport extends AbstractImport
{
	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_categories_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_categories;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->categories->item[0]['category_id']))
			ErrorHandler::fatalLang('lp_wrong_import_file');

		$items = $titles = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'category_id' => $categoryId = intval($item['category_id']),
					'icon'        => $item->icon,
					'description' => $item->description,
					'priority'    => intval($item['priority']),
					'status'      => intval($item['status']),
				];

				if ($item->titles) {
					foreach ($item->titles as $title) {
						foreach ($title as $k => $v) {
							$titles[] = [
								'item_id' => $categoryId,
								'type'    => 'category',
								'lang'    => $k,
								'title'   => $v,
							];
						}
					}
				}
			}
		}

		Db::$db->transaction('begin');

		$results = [];

		if ($items) {
			Utils::$context['import_successful'] = count($items);
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = Db::$db->insert('replace',
					'{db_prefix}lp_categories',
					[
						'category_id' => 'int',
						'icon'        => 'string',
						'description' => 'string',
						'priority'    => 'int',
						'status'      => 'int',
					],
					$items[$i],
					['category_id'],
					2
				);

				Utils::$context['lp_num_queries']++;
			}
		}

		$this->replaceTitles($titles, $results);

		$this->finish($results, 'categories');
	}
}
