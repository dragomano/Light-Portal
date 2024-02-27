<?php declare(strict_types=1);

/**
 * PageImport.php
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
use Bugo\Compat\{ErrorHandler, Lang, Theme, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class PageImport extends AbstractImport
{
	public function main(): void
	{
		User::mustHavePermission('admin_forum');

		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_import';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_import'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['page_area_info']  = Lang::$txt['lp_pages_import_info'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=import';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_import_description'],
		];

		Utils::$context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->pages->item[0]['page_id']))
			ErrorHandler::fatalLang('lp_wrong_import_file');

		$items = $titles = $params = $comments = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'page_id'      => $pageId = intval($item['page_id']),
					'category_id'  => intval($item['category_id']),
					'author_id'    => intval($item['author_id']),
					'alias'        => (string) $item->alias,
					'description'  => $item->description,
					'content'      => $item->content,
					'type'         => str_replace('md', 'markdown', (string) $item->type),
					'permissions'  => intval($item['permissions']),
					'status'       => intval($item['status']),
					'num_views'    => intval($item['num_views']),
					'num_comments' => intval($item['num_comments']),
					'created_at'   => intval($item['created_at']),
					'updated_at'   => intval($item['updated_at']),
				];

				if ($item->titles) {
					foreach ($item->titles as $title) {
						foreach ($title as $k => $v) {
							$titles[] = [
								'item_id' => $pageId,
								'type'    => 'page',
								'lang'    => $k,
								'title'   => $v,
							];
						}
					}
				}

				if ($item->comments) {
					foreach ($item->comments as $comment) {
						foreach ($comment as $v) {
							$comments[] = [
								'id'         => intval($v['id']),
								'parent_id'  => intval($v['parent_id']),
								'page_id'    => $pageId,
								'author_id'  => intval($v['author_id']),
								'message'    => $v->message,
								'created_at' => intval($v['created_at']),
							];
						}
					}
				}

				if ($item->params) {
					foreach ($item->params as $param) {
						foreach ($param as $k => $v) {
							$params[] = [
								'item_id' => $pageId,
								'type'    => 'page',
								'name'    => $k,
								'value'   => $v,
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
					'{db_prefix}lp_pages',
					[
						'page_id'      => 'int',
						'category_id'  => 'int',
						'author_id'    => 'int',
						'alias'        => 'string-255',
						'description'  => 'string-255',
						'content'      => 'string',
						'type'         => 'string',
						'permissions'  => 'int',
						'status'       => 'int',
						'num_views'    => 'int',
						'num_comments' => 'int',
						'created_at'   => 'int',
						'updated_at'   => 'int',
					],
					$items[$i],
					['page_id'],
					2
				);

				Utils::$context['lp_num_queries']++;
			}
		}

		$this->replaceTitles($titles, $results);

		if ($comments && $results) {
			$comments = array_chunk($comments, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				$results = Db::$db->insert('replace',
					'{db_prefix}lp_comments',
					[
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string-65534',
						'created_at' => 'int',
					],
					$comments[$i],
					['id', 'page_id'],
					2
				);

				Utils::$context['lp_num_queries']++;
			}
		}

		$this->replaceParams($params, $results);

		$this->finish($results, 'pages');
	}
}
