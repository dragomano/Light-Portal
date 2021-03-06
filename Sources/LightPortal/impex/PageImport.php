<?php

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\Helpers;

/**
 * PageImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PageImport extends AbstractImport
{
	/**
	 * Page import
	 *
	 * Импорт страниц
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageImpex');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_pages_import'];
		$context['page_area_title'] = $txt['lp_pages_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=import';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_import_description']
		);

		$context['sub_template'] = 'manage_import';

		$this->run();
	}

	/**
	 * Import from an XML file
	 *
	 * Импорт из XML-файла
	 *
	 * @return void
	 */
	protected function run()
	{
		global $db_temp_cache, $db_cache, $smcFunc;

		if (empty($_FILES['import_file']))
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$db_temp_cache = $db_cache;
		$db_cache = [];

		$file = $_FILES['import_file'];

		if ($file['type'] !== 'text/xml')
			return;

		$xml = simplexml_load_file($file['tmp_name']);

		if ($xml === false)
			return;

		if (!isset($xml->pages->item[0]['page_id']))
			fatal_lang_error('lp_wrong_import_file', false);

		$categories = $tags = $items = $titles = $params = $comments = [];

		foreach ($xml as $entity => $element) {
			if ($entity == 'categories') {
				foreach ($element->item as $item) {
					$categories[] = [
						'category_id' => intval($item['id']),
						'name'        => (string) $item['name'],
						'description' => (string) $item['desc'],
						'priority'    => intval($item['priority'])
					];
				}
			} elseif ($entity == 'tags') {
				foreach ($element->item as $item) {
					$tags[] = [
						'tag_id' => intval($item['id']),
						'value'  => (string) $item['value']
					];
				}
			} else {
				foreach ($element->item as $item) {
					$items[] = [
						'page_id'      => $page_id = intval($item['page_id']),
						'category_id'  => intval($item['category_id']),
						'author_id'    => intval($item['author_id']),
						'alias'        => (string) $item->alias,
						'description'  => $item->description,
						'content'      => $item->content,
						'type'         => (string) $item->type,
						'permissions'  => intval($item['permissions']),
						'status'       => intval($item['status']),
						'num_views'    => intval($item['num_views']),
						'num_comments' => intval($item['num_comments']),
						'created_at'   => intval($item['created_at']),
						'updated_at'   => intval($item['updated_at'])
					];

					if (!empty($item->titles)) {
						foreach ($item->titles as $title) {
							foreach ($title as $k => $v) {
								$titles[] = [
									'item_id' => $page_id,
									'type'    => 'page',
									'lang'    => $k,
									'title'   => $v
								];
							}
						}
					}

					if (!empty($item->comments)) {
						foreach ($item->comments as $comment) {
							foreach ($comment as $k => $v) {
								$comments[] = [
									'id'         => intval($v['id']),
									'parent_id'  => intval($v['parent_id']),
									'page_id'    => intval($page_id),
									'author_id'  => intval($v['author_id']),
									'message'    => $v->message,
									'created_at' => intval($v['created_at'])
								];
							}
						}
					}

					if (!empty($item->params)) {
						foreach ($item->params as $param) {
							foreach ($param as $k => $v) {
								$params[] = [
									'item_id' => $page_id,
									'type'    => 'page',
									'name'    => $k,
									'value'   => $v
								];
							}
						}
					}
				}
			}
		}

		if (!empty($categories)) {
			$result = $smcFunc['db_insert']('replace',
				'{db_prefix}lp_categories',
				array(
					'category_id' => 'int',
					'name'        => 'string',
					'description' => 'string',
					'priority'    => 'int'
				),
				$categories,
				array('category_id'),
				2
			);

			$smcFunc['lp_num_queries']++;
		}

		if (!empty($tags) && !empty($result)) {
			$tags  = array_chunk($tags, 100);
			$count = sizeof($tags);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_tags',
					array(
						'tag_id' => 'int',
						'value'  => 'string'
					),
					$tags[$i],
					array('tag_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($items) && !empty($result)) {
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_pages',
					array(
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
						'updated_at'   => 'int'
					),
					$items[$i],
					array('page_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($titles) && !empty($result)) {
			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_titles',
					array(
						'item_id' => 'int',
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string'
					),
					$titles[$i],
					array('item_id', 'type', 'lang'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($comments) && !empty($result)) {
			$comments = array_chunk($comments, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_comments',
					array(
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string-65534',
						'created_at' => 'int'
					),
					$comments[$i],
					array('id', 'page_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($params) && !empty($result)) {
			$params = array_chunk($params, 100);
			$count  = sizeof($params);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_params',
					array(
						'item_id' => 'int',
						'type'    => 'string',
						'name'    => 'string',
						'value'   => 'string'
					),
					$params[$i],
					array('item_id', 'type', 'name'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (empty($result))
			fatal_lang_error('lp_import_failed', false);

		// Restore the cache
		$db_cache = $db_temp_cache;

		Helpers::cache()->flush();
	}
}
