<?php declare(strict_types=1);

/**
 * TagImport.php
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

use Bugo\Compat\{Config, Db, ErrorHandler};
use Bugo\Compat\{Lang, Theme, Utils};

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class TagImport extends AbstractImport
{
	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_tags_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_tags_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_tags;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->tags->item[0]['tag_id']))
			ErrorHandler::fatalLang('lp_wrong_import_file');

		$items = $titles = $pages = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'tag_id' => $tagId = intval($item['tag_id']),
					'icon'   => $item->icon,
					'status' => intval($item['status']),
				];

				if ($item->titles) {
					foreach ($item->titles as $title) {
						foreach ($title as $k => $v) {
							$titles[] = [
								'item_id' => $tagId,
								'type'    => 'tag',
								'lang'    => $k,
								'title'   => $v,
							];
						}
					}
				}

				if ($item->pages) {
					foreach ($item->pages as $page) {
						foreach ($page as $v) {
							$pages[] = [
								'page_id' => intval($v['id']),
								'tag_id'  => $tagId,
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
					'{db_prefix}lp_tags',
					[
						'tag_id' => 'int',
						'icon'   => 'string',
						'status' => 'int',
					],
					$items[$i],
					['tag_id'],
					2
				);

				Utils::$context['lp_num_queries']++;
			}
		}

		$this->replaceTitles($titles, $results);

		if ($pages && $results) {
			$pages = array_chunk($pages, 100);
			$count = sizeof($pages);

			for ($i = 0; $i < $count; $i++) {
				$results = Db::$db->insert('replace',
					'{db_prefix}lp_page_tags',
					[
						'page_id' => 'int',
						'tag_id'  => 'int',
					],
					$pages[$i],
					['page_id', 'tag_id'],
					2
				);

				Utils::$context['lp_num_queries']++;
			}
		}

		$this->finish($results, 'tags');
	}
}
