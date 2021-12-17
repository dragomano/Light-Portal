<?php

/**
 * TinyPortal.php
 *
 * @package TinyPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.12.21
 */

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class TinyPortal extends Plugin
{
	public string $type = 'impex';

	public function addAdminAreas(array &$admin_areas)
	{
		global $user_info, $txt;

		if ($user_info['is_admin']) {
			$admin_areas['lp_portal']['areas']['lp_blocks']['subsections']['import_from_tp'] = array($txt['lp_tiny_portal']['label_name']);
			$admin_areas['lp_portal']['areas']['lp_pages']['subsections']['import_from_tp']  = array($txt['lp_tiny_portal']['label_name']);
		}
	}

	public function addBlockAreas(array &$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_tp'] = array(new BlockImport, 'main');
	}

	public function addPageAreas(array &$subActions)
	{
		global $user_info;

		if ($user_info['is_admin'])
			$subActions['import_from_tp'] = array(new PageImport, 'main');
	}

	public function importPages(array &$items, array &$titles, array &$params, array &$comments)
	{
		global $language, $modSettings;

		if (Helper::request('sa') !== 'import_from_tp')
			return;

		$comments = $this->getComments(array_keys($items));

		foreach ($items as $page_id => $item) {
			$items[$page_id]['num_comments'] = empty($comments[$page_id]) ? 0 : sizeof($comments[$page_id]);

			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $language,
				'title'   => $item['subject']
			];

			if ($language != 'english' && ! empty($modSettings['userLanguage'])) {
				$titles[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'lang'    => 'english',
					'title'   => $item['subject']
				];
			}

			unset($items[$page_id]['subject']);

			if (in_array('author', $items[$page_id]['options']) || in_array('date', $items[$page_id]['options']))
				$params[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'name'    => 'show_author_and_date',
					'value'   => 1
				];

			if (in_array('commentallow', $items[$page_id]['options']))
				$params[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'name'    => 'allow_comments',
					'value'   => 1
				];

			unset($items[$page_id]['options']);
		}
	}

	private function getComments(array $pages): array
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}tp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.member_id = mem.id_member)
			WHERE com.item_type = {string:type}' . (empty($pages) ? '' : '
				AND com.item_id IN ({array_int:pages})'),
			array(
				'type'  => 'article_comment',
				'pages' => $pages
			)
		);

		$comments = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if ($row['item_id'] < 0 || empty($row['comment']))
				continue;

			$comments[$row['item_id']][] = array(
				'id'         => $row['id'],
				'parent_id'  => 0,
				'page_id'    => $row['item_id'],
				'author_id'  => $row['member_id'],
				'message'    => $row['comment'],
				'created_at' => $row['datetime']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $comments;
	}
}
