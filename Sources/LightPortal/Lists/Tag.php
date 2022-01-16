<?php declare(strict_types=1);

/**
 * Tag.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Entities\Page;

if (! defined('SMF'))
	die('No direct access...');

final class Tag extends AbstractPageList
{
	public function show(Page $page)
	{
		if ($this->request()->has('id') === false)
			$this->showAll();

		$this->context['lp_tag'] = $this->request('id', 0);

		if (array_key_exists($this->context['lp_tag'], $this->getAllTags()) === false) {
			$this->context['error_link'] = LP_BASE_URL . ';sa=tags';
			$this->txt['back'] = $this->txt['lp_all_page_tags'];
			fatal_lang_error('lp_tag_not_found', false, null, 404);
		}

		$this->context['page_title']     = sprintf($this->txt['lp_all_tags_by_key'], $this->getAllTags()[$this->context['lp_tag']]);
		$this->context['canonical_url']  = LP_BASE_URL . ';sa=tags;id=' . $this->context['lp_tag'];
		$this->context['robot_no_index'] = true;

		$this->context['linktree'][] = [
			'name' => $this->txt['lp_all_page_tags'],
			'url'  => LP_BASE_URL . ';sa=tags'
		];

		$this->context['linktree'][] = [
			'name' => $this->context['page_title']
		];

		if (! empty($this->modSettings['lp_show_items_as_articles']))
			$page->showAsCards($this);

		$listOptions = $page->getList();
		$listOptions['id'] = 'lp_tags';
		$listOptions['get_items'] = [
			'function' => [$this, 'getPages']
		];
		$listOptions['get_count'] = [
			'function' => [$this, 'getTotalCountPages']
		];

		$this->require('Subs-List');
		createList($listOptions);

		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = 'lp_tags';

		obExit();
	}

	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				mem.real_name AS author_name, ps.value, t.title
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})
			WHERE FIND_IN_SET({int:id}, ps.value) > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'guest'        => $this->txt['guest_title'],
				'lang'         => $this->user_info['language'],
				'id'           => $this->context['lp_tag'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			]
		);

		$rows = $this->smcFunc['db_fetch_all']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $this->getPreparedResults($rows);
	}

	public function getTotalCountPages(): int
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
			WHERE FIND_IN_SET({int:id}, ps.value) > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			[
				'id'           => $this->context['lp_tag'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions()
			]
		);

		[$num_items] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_items;
	}

	public function showAll()
	{
		$this->context['page_title']     = $this->txt['lp_all_page_tags'];
		$this->context['canonical_url']  = LP_BASE_URL . ';sa=tags';
		$this->context['robot_no_index'] = true;

		$this->context['linktree'][] = [
			'name' => $this->context['page_title']
		];

		$listOptions = [
			'id' => 'tags',
			'items_per_page' => $this->modSettings['defaultMaxListItems'] ?: 50,
			'title' => $this->context['page_title'],
			'no_items_label' => $this->txt['lp_no_tags'],
			'base_href' => $this->context['canonical_url'],
			'default_sort_col' => 'value',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => fn() => count($this->getAll())
			],
			'columns' => [
				'value' => [
					'header' => [
						'value' => $this->txt['lp_keyword_column']
					],
					'data' => [
						'function' => fn($entry) => '<a href="' . $entry['link'] . '">' . $entry['value'] . '</a>',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 't.value DESC',
						'reverse' => 't.value'
					]
				],
				'frequency' => [
					'header' => [
						'value' => $this->txt['lp_frequency_column']
					],
					'data' => [
						'db'    => 'frequency',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'num DESC',
						'reverse' => 'num'
					]
				]
			],
			'form' => [
				'href' => $this->context['canonical_url']
			]
		];

		$this->require('Subs-List');
		createList($listOptions);

		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = 'tags';

		obExit();
	}

	public function getList(): array
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			ORDER BY value',
			[]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['tag_id']] = $row['value'];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 't.value'): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT t.tag_id, t.value, COUNT(t.tag_id) AS num
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
				INNER JOIN {db_prefix}lp_tags AS t ON (FIND_IN_SET(t.tag_id, ps.value) > 0)
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY t.tag_id, t.value
			ORDER BY {raw:sort}' . ($items_per_page ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['tag_id']] = [
				'value'     => $row['value'],
				'link'      => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id'],
				'frequency' => $row['num']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
