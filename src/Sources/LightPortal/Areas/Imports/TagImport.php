<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\{Config, ErrorHandler};
use Bugo\Compat\{Lang, Theme, Utils};

use function intval;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class TagImport extends AbstractImport
{
	protected string $entity = 'tags';

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

		if (! isset($xml->tags->item[0]['tag_id'])) {
			ErrorHandler::fatalLang('lp_wrong_import_file');
		}

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
								'value'   => $v,
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

		$this->startTransaction($items);

		$results = $this->insertData(
			'lp_tags',
			'replace',
			$items,
			[
				'tag_id' => 'int',
				'icon'   => 'string',
				'status' => 'int',
			],
			['tag_id'],
		);

		$this->replaceTitles($titles, $results);

		if ($results) {
			$results = $this->insertData(
				'lp_page_tag',
				'replace',
				$pages,
				[
					'page_id' => 'int',
					'tag_id'  => 'int',
				],
				['page_id', 'tag_id'],
			);
		}

		$this->finishTransaction($results);
	}
}
