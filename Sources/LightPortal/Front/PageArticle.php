<?php declare(strict_types=1);

/**
 * PageArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Front;

if (! defined('SMF'))
	die('No direct access...');

class PageArticle extends AbstractArticle
{
	protected array $selected_categories = [];

	public function init()
	{
		$this->selected_categories = empty($this->modSettings['lp_frontpage_categories']) ? [] : explode(',', $this->modSettings['lp_frontpage_categories']);

		if (empty($this->selected_categories) && $this->modSettings['lp_frontpage_mode'] === 'all_pages')
			$this->selected_categories = [0];

		$this->params = [
			'status'              => 1,
			'current_time'        => time(),
			'permissions'         => $this->getPermissions(),
			'selected_categories' => $this->selected_categories
		];

		$this->orders = [
			'CASE WHEN com.created_at > 0 THEN 0 ELSE 1 END, comment_date DESC',
			'p.created_at DESC',
			'p.created_at',
			'date DESC'
		];

		$this->hook('frontPages', [&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders]);
	}

	public function getData(int $start, int $limit): array
	{
		$titles = $this->getEntityList('title');

		$this->params += [
			'start' => $start,
			'limit' => $limit
		];

		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.content, p.description, p.type, p.status, p.num_views,
				CASE WHEN COALESCE(CAST(par.value AS integer), 0) > 0 THEN p.num_comments ELSE 0 END AS num_comments, p.created_at,
				GREATEST(p.created_at, p.updated_at) AS date, cat.name AS category_name, mem.real_name AS author_name,
				com.created_at AS comment_date, com.author_id AS comment_author_id, mem2.real_name AS comment_author_name, com.message AS comment_message' . (empty($this->columns) ? '' : ', ' . implode(', ', $this->columns)) . '
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS cat ON (cat.category_id = p.category_id)
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_comments AS com ON (p.last_comment_id = com.id)
				LEFT JOIN {db_prefix}members AS mem2 ON (com.author_id = mem2.id_member)
				LEFT JOIN {db_prefix}lp_params AS par ON (par.item_id = com.page_id AND par.type = {literal:page} AND par.name = {literal:allow_comments})' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . (empty($this->selected_categories) ? '' : '
				AND p.category_id IN ({array_int:selected_categories})') . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty($this->modSettings['lp_frontpage_order_by_replies']) ? '' : 'num_comments DESC, ') . $this->orders[$this->modSettings['lp_frontpage_article_sorting'] ?? 0] . '
			LIMIT {int:start}, {int:limit}',
			$this->params
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			if (! isset($pages[$row['page_id']])) {
				$row['content'] = parse_content($row['content'], $row['type']);

				$pages[$row['page_id']] = [
					'id' => $row['page_id'],
					'section' => [
						'name' => empty($row['category_id']) ? '' : $row['category_name'],
						'link' => empty($row['category_id']) ? '' : (LP_BASE_URL . ';sa=categories;id=' . $row['category_id'])
					],
					'author' => [
						'id' => $author_id = (int) (empty($this->modSettings['lp_frontpage_article_sorting']) && $row['num_comments'] ? $row['comment_author_id'] : $row['author_id']),
						'link' => $this->scripturl . '?action=profile;u=' . $author_id,
						'name' => empty($this->modSettings['lp_frontpage_article_sorting']) && $row['num_comments'] ? $row['comment_author_name'] : $row['author_name']
					],
					'date' => empty($this->modSettings['lp_frontpage_article_sorting']) && $row['comment_date'] ? $row['comment_date'] : $row['created_at'],
					'link' => LP_PAGE_URL . $row['alias'],
					'views' => [
						'num' => $row['num_views'],
						'title' => $this->txt['lp_views'],
						'after' => ''
					],
					'replies' => [
						'num' => $this->modSettings['lp_show_comment_block'] && $this->modSettings['lp_show_comment_block'] === 'default' ? $row['num_comments'] : 0,
						'title' => $this->txt['lp_comments'],
						'after' => ''
					],
					'is_new' => $this->user_info['last_login'] < $row['date'] && $row['author_id'] != $this->user_info['id'],
					'image' => empty($this->modSettings['lp_show_images_in_articles']) ? '' : $this->getImageFromText($row['content']),
					'can_edit' => $this->user_info['is_admin'] || $this->context['allow_light_portal_manage_pages_any'] || ($this->context['allow_light_portal_manage_pages_own'] && $row['author_id'] == $this->user_info['id']),
					'edit_link' => $this->scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
				];

				if (! empty($this->modSettings['lp_show_teaser']))
					$pages[$row['page_id']]['teaser'] = $this->getTeaser(empty($this->modSettings['lp_frontpage_article_sorting']) && $row['num_comments'] ? $this->parseBbc($row['comment_message']) : ($row['description'] ?: $row['content']));

				if (! empty($this->modSettings['lp_frontpage_article_sorting']) && $this->modSettings['lp_frontpage_article_sorting'] == 3)
					$pages[$row['page_id']]['date'] = $row['date'];
			}

			$pages[$row['page_id']]['title'] = $titles[$row['page_id']];

			$this->hook('frontPagesOutput', [&$pages, $row]);
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		$pages = $this->getItemsWithUserAvatars($pages);

		$this->prepareTags($pages);

		return $pages;
	}

	public function getTotalCount(): int
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . (empty($this->selected_categories) ? '' : '
				AND p.category_id IN ({array_int:selected_categories})') . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params
		);

		[$num_pages] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_pages;
	}

	private function prepareTags(array &$pages)
	{
		if (empty($pages))
			return;

		$request = $this->smcFunc['db_query']('', '
			SELECT t.tag_id, t.value, p.item_id
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_params AS p ON (p.type = {literal:page} AND p.name = {literal:keywords})
			WHERE p.item_id IN ({array_int:pages})
				AND FIND_IN_SET(t.tag_id, p.value) > 0
			ORDER BY t.value',
			[
				'pages' => array_keys($pages)
			]
		);

		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$pages[$row['item_id']]['tags'][] = [
				'name' => $row['value'],
				'href' => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;
	}
}
