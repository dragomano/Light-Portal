<?php

declare(strict_types = 1);

/**
 * PageArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\{Addon, Helper};

if (! defined('SMF'))
	die('No direct access...');

class PageArticle extends AbstractArticle
{
	protected array $selected_categories = [];

	public function init()
	{
		global $modSettings;

		$this->selected_categories = empty($modSettings['lp_frontpage_categories']) ? [] : explode(',', $modSettings['lp_frontpage_categories']);

		if (empty($this->selected_categories) && $modSettings['lp_frontpage_mode'] === 'all_pages')
			$this->selected_categories = [0];

		$this->params = [
			'status'              => 1,
			'current_time'        => time(),
			'permissions'         => Helper::getPermissions(),
			'selected_categories' => $this->selected_categories
		];

		$this->orders = [
			'CASE WHEN (SELECT lp_com.created_at FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id LIMIT 1) > 0 THEN 0 ELSE 1 END, comment_date DESC',
			'p.created_at DESC',
			'p.created_at',
			'date DESC'
		];

		Addon::run('frontPages', array(&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders));
	}

	public function getData(int $start, int $limit): array
	{
		global $smcFunc, $user_info, $modSettings, $scripturl, $txt;

		$titles = Helper::getAllTitles();
		$categories = Helper::getAllCategories();

		$this->params += array(
			'start' => $start,
			'limit' => $limit
		);

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.content, p.description, p.type, p.status, p.num_views, p.num_comments, p.created_at,
				GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name,
				(SELECT lp_com.created_at FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_date, (SELECT lp_com.author_id FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_author_id, (SELECT real_name FROM {db_prefix}lp_comments AS lp_com LEFT JOIN {db_prefix}members ON (lp_com.author_id = id_member) WHERE lp_com.page_id = p.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_author_name, (SELECT lp_com.message FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_message' . (empty($this->columns) ? '' : ', ' . implode(', ', $this->columns)) . '
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . (empty($this->selected_categories) ? '' : '
				AND p.category_id IN ({array_int:selected_categories})') . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty($modSettings['lp_frontpage_order_by_num_replies']) ? '' : 'num_comments DESC, ') . $this->orders[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
			LIMIT {int:start}, {int:limit}',
			$this->params
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (! isset($pages[$row['page_id']])) {
				Helper::parseContent($row['content'], $row['type']);

				$pages[$row['page_id']] = array(
					'id' => $row['page_id'],
					'section' => array(
						'name' => empty($row['category_id']) ? '' : $categories[$row['category_id']]['name'],
						'link' => empty($row['category_id']) ? '' : $scripturl . '?action=' . LP_ACTION . ';sa=categories;id=' . $row['category_id']
					),
					'author' => array(
						'id' => $author_id = (int) (empty($modSettings['lp_frontpage_article_sorting']) && ! empty($row['num_comments']) ? $row['comment_author_id'] : $row['author_id']),
						'link' => $scripturl . '?action=profile;u=' . $author_id,
						'name' => empty($modSettings['lp_frontpage_article_sorting']) && ! empty($row['num_comments']) ? $row['comment_author_name'] : $row['author_name']
					),
					'date' => empty($modSettings['lp_frontpage_article_sorting']) && ! empty($row['comment_date']) ? $row['comment_date'] : $row['created_at'],
					'link' => $scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias'],
					'views' => array(
						'num' => $row['num_views'],
						'title' => $txt['lp_views'],
						'after' => ''
					),
					'replies' => array(
						'num' => ! empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] === 'default' ? $row['num_comments'] : 0,
						'title' => $txt['lp_comments'],
						'after' => ''
					),
					'is_new' => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
					'image' => empty($modSettings['lp_show_images_in_articles']) ? '' : Helper::getImageFromText($row['content']),
					'can_edit' => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id']),
					'edit_link' => $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
				);

				$pages[$row['page_id']]['author']['avatar'] = Helper::getUserAvatar($author_id)['href'] ?? '';

				if (! empty($modSettings['lp_show_teaser']))
					$pages[$row['page_id']]['teaser'] = Helper::getTeaser(empty($modSettings['lp_frontpage_article_sorting']) && ! empty($row['num_comments']) ? parse_bbc($row['comment_message']) : ($row['description'] ?: $row['content']));

				if (! empty($modSettings['lp_frontpage_article_sorting']) && $modSettings['lp_frontpage_article_sorting'] == 3)
					$pages[$row['page_id']]['date'] = $row['date'];
			}

			$pages[$row['page_id']]['title'] = $titles[$row['page_id']];

			Addon::run('frontPagesOutput', array(&$pages, $row));
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		$this->prepareTags($pages);

		return $pages;
	}

	public function getTotalCount(): int
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
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

		[$num_pages] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $num_pages;
	}

	private function prepareTags(array &$pages)
	{
		global $smcFunc, $scripturl;

		if (empty($pages))
			return;

		$request = $smcFunc['db_query']('', '
			SELECT t.tag_id, t.value, p.item_id
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_params AS p ON (p.type = {literal:page} AND p.name = {literal:keywords})
			WHERE p.item_id IN ({array_int:pages})
				AND FIND_IN_SET(t.tag_id, p.value)
			ORDER BY t.value',
			array(
				'pages' => array_keys($pages)
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[$row['item_id']]['tags'][] = array(
				'name' => $row['value'],
				'href' => $scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $row['tag_id']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;
	}
}
