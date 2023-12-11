<?php

/**
 * TinyPortal.php
 *
 * @package TinyPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 10.12.23
 */

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class TinyPortal extends Plugin
{
	public string $type = 'impex';

	public function updateAdminAreas(array &$areas): void
	{
		if ($this->user_info['is_admin']) {
			$areas['lp_blocks']['subsections']['import_from_tp'] = [$this->context['lp_icon_set']['import'] . $this->txt['lp_tiny_portal']['label_name']];
			$areas['lp_pages']['subsections']['import_from_tp']  = [$this->context['lp_icon_set']['import'] . $this->txt['lp_tiny_portal']['label_name']];
		}
	}

	public function updateBlockAreas(array &$areas): void
	{
		if ($this->user_info['is_admin'])
			$areas['import_from_tp'] = [new BlockImport, 'main'];
	}

	public function updatePageAreas(array &$areas): void
	{
		if ($this->user_info['is_admin'])
			$areas['import_from_tp'] = [new PageImport, 'main'];
	}

	public function importPages(array &$items, array &$titles, array &$params, array &$comments): void
	{
		if ($this->request('sa') !== 'import_from_tp')
			return;

		$comments = $this->getComments(array_keys($items));

		foreach ($items as $page_id => $item) {
			$items[$page_id]['num_comments'] = empty($comments[$page_id]) ? 0 : sizeof($comments[$page_id]);

			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $this->language,
				'title'   => $item['subject']
			];

			if ($this->language != 'english' && ! empty($this->modSettings['userLanguage'])) {
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
		$result = $this->smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}tp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.member_id = mem.id_member)
			WHERE com.item_type = {string:type}' . (empty($pages) ? '' : '
				AND com.item_id IN ({array_int:pages})'),
			[
				'type'  => 'article_comment',
				'pages' => $pages
			]
		);

		$comments = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			if ($row['item_id'] < 0 || empty($row['comment']))
				continue;

			$comments[$row['item_id']][] = [
				'id'         => $row['id'],
				'parent_id'  => 0,
				'page_id'    => $row['item_id'],
				'author_id'  => $row['member_id'],
				'message'    => $row['comment'],
				'created_at' => $row['datetime']
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $comments;
	}
}
