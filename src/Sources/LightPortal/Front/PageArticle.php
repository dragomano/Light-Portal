<?php declare(strict_types=1);

/**
 * PageArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Utils\{BBCodeParser, Config, Lang, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

class PageArticle extends AbstractArticle
{
	protected array $selected_categories = [];

	public function init(): void
	{
		$this->selected_categories = empty(Config::$modSettings['lp_frontpage_categories']) ? [] : explode(',', Config::$modSettings['lp_frontpage_categories']);

		if (empty($this->selected_categories) && Config::$modSettings['lp_frontpage_mode'] === 'all_pages')
			$this->selected_categories = [0];

		$this->params = [
			'status'              => Page::STATUS_ACTIVE,
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

		$this->hook('frontPages', [&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders]);
	}

	public function getData(int $start, int $limit): array
	{
		$titles = $this->getEntityList('title');

		$this->params += [
			'start' => $start,
			'limit' => $limit
		];

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.content, p.description, p.type, p.status, p.num_views,
				CASE WHEN COALESCE(par.value, \'0\') != \'0\' THEN p.num_comments ELSE 0 END AS num_comments, p.created_at,
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
			ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 'num_comments DESC, ') . $this->orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
			LIMIT {int:start}, {int:limit}',
			$this->params
		);

		$pages = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if (! isset($pages[$row['page_id']])) {
				$row['content'] = parse_content($row['content'], $row['type']);

				$pages[$row['page_id']] = [
					'id' => $row['page_id'],
					'section' => [
						'name' => empty($row['category_id']) ? '' : $row['category_name'],
						'link' => empty($row['category_id']) ? '' : (LP_BASE_URL . ';sa=categories;id=' . $row['category_id'])
					],
					'author' => [
						'id' => $author_id = (int) (empty(Config::$modSettings['lp_frontpage_article_sorting']) && $row['num_comments'] ? $row['comment_author_id'] : $row['author_id']),
						'link' => Config::$scripturl . '?action=profile;u=' . $author_id,
						'name' => empty(Config::$modSettings['lp_frontpage_article_sorting']) && $row['num_comments'] ? $row['comment_author_name'] : $row['author_name']
					],
					'date' => empty(Config::$modSettings['lp_frontpage_article_sorting']) && $row['comment_date'] ? $row['comment_date'] : $row['created_at'],
					'link' => LP_PAGE_URL . $row['alias'],
					'views' => [
						'num' => $row['num_views'],
						'title' => Lang::$txt['lp_views'],
						'after' => ''
					],
					'replies' => [
						'num' => Config::$modSettings['lp_show_comment_block'] && Config::$modSettings['lp_show_comment_block'] === 'default' ? $row['num_comments'] : 0,
						'title' => Lang::$txt['lp_comments'],
						'after' => ''
					],
					'is_new' => User::$info['last_login'] < $row['date'] && $row['author_id'] != User::$info['id'],
					'image' => empty(Config::$modSettings['lp_show_images_in_articles']) ? '' : $this->getImageFromText($row['content']),
					'can_edit' => User::$info['is_admin'] || Utils::$context['allow_light_portal_manage_pages_any'] || (Utils::$context['allow_light_portal_manage_pages_own'] && $row['author_id'] == User::$info['id']),
					'edit_link' => Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
				];

				if (! empty(Config::$modSettings['lp_show_teaser']))
					$pages[$row['page_id']]['teaser'] = $this->getTeaser(empty(Config::$modSettings['lp_frontpage_article_sorting']) && $row['num_comments'] ? BBCodeParser::load()->parse($row['comment_message']) : ($row['description'] ?: $row['content']));

				if (! empty(Config::$modSettings['lp_frontpage_article_sorting']) && Config::$modSettings['lp_frontpage_article_sorting'] == 3)
					$pages[$row['page_id']]['date'] = $row['date'];
			}

			$pages[$row['page_id']]['title'] = $this->getTranslatedTitle($titles[$row['page_id']]);

			$this->hook('frontPagesOutput', [&$pages, $row]);
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		$pages = $this->getItemsWithUserAvatars($pages);

		$this->prepareTags($pages);

		return $pages;
	}

	public function getTotalCount(): int
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
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

		[$num_pages] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $num_pages;
	}

	private function prepareTags(array &$pages): void
	{
		if (empty($pages))
			return;

		$result = Utils::$smcFunc['db_query']('', '
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

		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$pages[$row['item_id']]['tags'][] = [
				'name' => $row['value'],
				'href' => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id']
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;
	}
}
