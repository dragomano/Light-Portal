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

use Bugo\Compat\{Config, ErrorHandler};
use Bugo\Compat\{Lang, Theme, User, Utils};
use Bugo\LightPortal\Areas\Imports\Traits\WithComments;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @property mixed|void $item
 */
final class PageImport extends AbstractImport
{
	use WithComments;

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

		if (! isset($xml->pages->item[0]['page_id'])) {
			ErrorHandler::fatalLang('lp_wrong_import_file');
		}

		$items = $titles = $params = $comments = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'page_id'      => $pageId = intval($item['page_id']),
					'category_id'  => intval($item['category_id']),
					'author_id'    => intval($item['author_id']),
					'slug'         => (string) ($item->alias ?? $item->slug),
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
								'value'   => $v,
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

		$this->startTransaction($items);

		$results = $this->insertData(
			'lp_pages',
			'replace',
			$items,
			[
				'page_id'      => 'int',
				'category_id'  => 'int',
				'author_id'    => 'int',
				'slug'         => 'string-255',
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
			['page_id'],
		);

		$this->replaceTitles($titles, $results);
		$this->replaceParams($params, $results);
		$this->replaceComments($comments, $results);
		$this->finishTransaction($results, 'pages');
	}
}
