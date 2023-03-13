<?php declare(strict_types=1);

/**
 * Category.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Entities\Page;

if (! defined('SMF'))
	die('No direct access...');

final class Category extends AbstractPageList
{
	public function show(Page $page)
	{
		if ($this->request()->has('id') === false)
			$this->showAll();

		$this->context['lp_category'] = $this->request('id');

		if (array_key_exists($this->context['lp_category'], $this->getAllCategories()) === false) {
			$this->context['error_link'] = LP_BASE_URL . ';sa=categories';
			$this->txt['back'] = $this->txt['lp_all_categories'];
			$this->fatalLangError('lp_category_not_found', 404);
		}

		if (empty($this->context['lp_category'])) {
			$this->context['page_title'] = $this->txt['lp_all_pages_without_category'];
		} else {
			$category = $this->getAllCategories()[$this->context['lp_category']];
			$this->context['page_title'] = sprintf($this->txt['lp_all_pages_with_category'], $category['name']);
		}

		$this->context['description'] = $category['desc'] ?? '';

		$this->context['canonical_url']  = LP_BASE_URL . ';sa=categories;id=' . $this->context['lp_category'];
		$this->context['robot_no_index'] = true;

		$this->context['linktree'][] = [
			'name' => $this->txt['lp_all_categories'],
			'url'  => LP_BASE_URL . ';sa=categories'
		];

		$this->context['linktree'][] = [
			'name' => $this->context['page_title']
		];

		if (! empty($this->modSettings['lp_show_items_as_articles']))
			$page->showAsCards($this);

		$listOptions = $page->getList();
		$listOptions['id'] = 'lp_categories';
		$listOptions['get_items'] = [
			'function' => [$this, 'getPages']
		];
		$listOptions['get_count'] = [
			'function' => [$this, 'getTotalCountPages']
		];

		if (isset($category['desc'])) {
			$listOptions['additional_rows'] = [
				[
					'position' => 'top_of_list',
					'value'    => $category['desc'],
					'class'    => 'information'
				]
			];
		}

		$this->createList($listOptions);

		$this->obExit();
	}

	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.content, p.description, p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				mem.real_name AS author_name, t.title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})
			WHERE p.category_id = {int:id}
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'guest'        => $this->txt['guest_title'],
				'lang'         => $this->user_info['language'],
				'id'           => $this->context['lp_category'],
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
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE category_id = {string:id}
				AND status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})',
			[
				'id'           => $this->context['lp_category'],
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
		$this->context['page_title']     = $this->txt['lp_all_categories'];
		$this->context['canonical_url']  = LP_BASE_URL . ';sa=categories';
		$this->context['robot_no_index'] = true;

		$this->context['linktree'][] = [
			'name' => $this->context['page_title']
		];

		$listOptions = [
			'id' => 'categories',
			'items_per_page' => $this->modSettings['defaultMaxListItems'] ?: 50,
			'title' => $this->context['page_title'],
			'no_items_label' => $this->txt['lp_no_categories'],
			'base_href' => $this->context['canonical_url'],
			'default_sort_col' => 'name',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => fn() => count($this->getAll())
			],
			'columns' => [
				'name' => [
					'header' => [
						'value' => $this->txt['lp_category']
					],
					'data' => [
						'function' => fn($entry) => '<a href="' . $entry['link'] . '">' . $entry['name'] . '</a>' .
							(empty($entry['desc']) ? '' : '<p class="smalltext">' . $entry['desc'] . '</p>')
					],
					'sort' => [
						'default' => 'c.name DESC',
						'reverse' => 'c.name'
					]
				],
				'num_pages' => [
					'header' => [
						'value' => $this->txt['lp_total_pages_column']
					],
					'data' => [
						'db'    => 'num_pages',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'frequency DESC',
						'reverse' => 'frequency'
					]
				]
			],
			'form' => [
				'href' => $this->context['canonical_url']
			]
		];

		$this->createList($listOptions);

		$this->obExit();
	}

	public function getList(): array
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT category_id, name, description, priority
			FROM {db_prefix}lp_categories
			ORDER BY priority',
			[]
		);

		$items = [0 => ['name' => $this->txt['lp_no_category']]];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['category_id']] = [
				'id'       => $row['category_id'],
				'name'     => $row['name'],
				'desc'     => $row['description'],
				'priority' => $row['priority']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'c.name'): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT COALESCE(c.category_id, 0) AS category_id, c.name, c.description, COUNT(p.page_id) AS frequency
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS c ON (p.category_id = c.category_id)
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY c.category_id, c.name, c.description
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
			if ($row['description'] && str_contains($row['description'], ']')) {
				$row['description'] = $this->parseBbc($row['description']);
			}

			$items[$row['category_id']] = [
				'name'      => $row['name'] ?: $this->txt['lp_no_category'],
				'desc'      => $row['description'] ?? '',
				'link'      => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
				'num_pages' => $row['frequency']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
