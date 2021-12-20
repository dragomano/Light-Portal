<?php

declare(strict_types = 1);

/**
 * Category.php
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

use Bugo\LightPortal\{Helper, Page};

if (! defined('SMF'))
	die('Hacking attempt...');

final class Category implements PageListInterface
{
	public function show()
	{
		global $context, $txt, $scripturl, $modSettings;

		if (Helper::request()->has('id') === false)
			$this->showAll();

		$context['lp_category'] = Helper::request('id');

		if (array_key_exists($context['lp_category'], Helper::getAllCategories()) === false) {
			$this->changeBackButton();
			fatal_lang_error('lp_category_not_found', false, null, 404);
		}

		if (empty($context['lp_category'])) {
			$context['page_title'] = $txt['lp_all_pages_without_category'];
		} else {
			$category = Helper::getAllCategories()[$context['lp_category']];
			$context['page_title'] = sprintf($txt['lp_all_pages_with_category'], $category['name']);
		}

		$context['description'] = $category['desc'] ?? '';

		$context['canonical_url']  = $scripturl . '?action=' . LP_ACTION . ';sa=categories;id=' . $context['lp_category'];
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $txt['lp_all_categories'],
			'url'  => $scripturl . '?action=' . LP_ACTION . ';sa=categories'
		);

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		if (! empty($modSettings['lp_show_items_as_articles']))
			(new Page)->showAsCards($this);

		$listOptions = (new Page)->getList();
		$listOptions['id'] = 'lp_categories';
		$listOptions['get_items'] = array(
			'function' => array($this, 'getPages')
		);
		$listOptions['get_count'] = array(
			'function' => array($this, 'getTotalCountPages')
		);

		if (! empty($category['desc'])) {
			$listOptions['additional_rows'] = array(
				array(
					'position' => 'top_of_list',
					'value'    => $category['desc'],
					'class'    => 'information'
				)
			);
		}

		Helper::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'lp_categories';

		obExit();
	}

	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		global $smcFunc, $txt, $user_info, $context;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.content, p.description, p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, {string:guest}) AS author_name, t.title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})
			WHERE p.category_id = {int:id}
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array(
				'guest'        => $txt['guest_title'],
				'lang'         => $user_info['language'],
				'id'           => $context['lp_category'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helper::getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			)
		);

		$items = [];
		$page  = new Page;
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$page->fetchQueryResults($items, $row);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	public function getTotalCountPages(): int
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE category_id = {string:id}
				AND status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})',
			array(
				'id'           => $context['lp_category'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helper::getPermissions()
			)
		);

		[$num_items] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $num_items;
	}

	public function showAll()
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['page_title']     = $txt['lp_all_categories'];
		$context['canonical_url']  = $scripturl . '?action=' . LP_ACTION . ';sa=categories';
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$listOptions = array(
			'id' => 'categories',
			'items_per_page' => $modSettings['defaultMaxListItems'] ?: 50,
			'title' => $context['page_title'],
			'no_items_label' => $txt['lp_no_categories'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'name',
			'get_items' => array(
				'function' => array($this, 'getAll')
			),
			'get_count' => array(
				'function' => fn() => count($this->getAll())
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['lp_category']
					),
					'data' => array(
						'function' => fn($entry) => '<a href="' . $entry['link'] . '">' . $entry['name'] . '</a>' .
							(empty($entry['desc']) ? '' : '<p class="smalltext">' . $entry['desc'] . '</p>')
					),
					'sort' => array(
						'default' => 'c.name DESC',
						'reverse' => 'c.name'
					)
				),
				'num_pages' => array(
					'header' => array(
						'value' => $txt['lp_total_pages_column']
					),
					'data' => array(
						'db'    => 'num_pages',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'frequency DESC',
						'reverse' => 'frequency'
					)
				)
			),
			'form' => array(
				'href' => $context['canonical_url']
			)
		);

		Helper::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'categories';

		obExit();
	}

	public function getList(): array
	{
		global $smcFunc, $txt;

		$request = $smcFunc['db_query']('', '
			SELECT category_id, name, description, priority
			FROM {db_prefix}lp_categories
			ORDER BY priority',
			array()
		);

		$items = [0 => ['name' => $txt['lp_no_category']]];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['category_id']] = array(
				'id'       => $row['category_id'],
				'name'     => $row['name'],
				'desc'     => $row['description'],
				'priority' => $row['priority']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'c.name'): array
	{
		global $smcFunc, $scripturl, $txt;

		$request = $smcFunc['db_query']('', '
			SELECT COALESCE(c.category_id, 0) AS category_id, c.name, c.description, COUNT(p.page_id) AS frequency
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS c ON (p.category_id = c.category_id)
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY c.category_id, c.name, c.description
			ORDER BY {raw:sort}' . ($items_per_page ? '
			LIMIT {int:start}, {int:limit}' : ''),
			array(
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helper::getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (! empty($row['description']) && strpos($row['description'], ']') !== false) {
				$row['description'] = parse_bbc($row['description']);
			}

			$items[$row['category_id']] = array(
				'name'      => $row['name'] ?: $txt['lp_no_category'],
				'desc'      => $row['description'] ?? '',
				'link'      => $scripturl . '?action=' . LP_ACTION . ';sa=categories;id=' . $row['category_id'],
				'num_pages' => $row['frequency']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	public function updatePriority(array $categories)
	{
		global $smcFunc;

		if (empty($categories))
			return;

		$conditions = '';
		foreach ($categories as $priority => $item) {
			$conditions .= ' WHEN category_id = ' . $item . ' THEN ' . $priority;
		}

		if (empty($conditions))
			return;

        $smcFunc['db_query']('', '
            UPDATE {db_prefix}lp_categories
            SET priority = CASE ' . $conditions . ' ELSE priority END
            WHERE category_id IN ({array_int:categories})',
            array(
                'categories' => $categories
            )
        );

        $smcFunc['lp_num_queries']++;
	}

	public function add(string $name, string $desc = '')
	{
		global $smcFunc;

		if (empty($name))
			return;

		loadTemplate('LightPortal/ManageSettings');

		$result['error'] = true;

		$item = $smcFunc['db_insert']('',
			'{db_prefix}lp_categories',
			array(
				'name'        => 'string',
				'description' => 'string',
				'priority'    => 'int'
			),
			array(
				$name,
				$desc,
				$this->getPriority()
			),
			array('category_id'),
			1
		);

		$smcFunc['lp_num_queries']++;

		if (! empty($item)) {
			ob_start();

			show_single_category($item, ['name' => $name, 'desc' => $desc]);

			$new_cat = ob_get_clean();

			$result = [
				'success' => true,
				'section' => $new_cat,
				'item'    => $item
			];
		}

		Helper::cache()->forget('all_categories');

		exit(json_encode($result));
	}

	public function updateName(int $item, string $value)
	{
		global $smcFunc;

		if (empty($item))
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_categories
			SET name = {string:name}
			WHERE category_id = {int:item}',
			array(
				'name' => $value,
				'item' => $item
			)
		);

		$smcFunc['lp_num_queries']++;
	}

	public function updateDescription(int $item, string $value)
	{
		global $smcFunc;

		if (empty($item))
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_categories
			SET description = {string:desc}
			WHERE category_id = {int:item}',
			array(
				'desc' => $value,
				'item' => $item
			)
		);

		$smcFunc['lp_num_queries']++;
	}

	public function remove(array $items)
	{
		global $smcFunc;

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_categories
			WHERE category_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category}
			WHERE category_id IN ({array_int:items})',
			array(
				'category' => 0,
				'items'    => $items
			)
		);

		$smcFunc['lp_num_queries'] += 2;

		Helper::cache()->flush();
	}

	private function getPriority(): int
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_categories',
			array()
		);

		[$priority] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $priority;
	}

	/**
	 * Change back button text and back button href
	 *
	 * Меняем текст и href кнопки «Назад»
	 */
	private function changeBackButton()
	{
		global $txt;

		addInlineJavaScript('
		const backButton = document.querySelector("#fatal_error + .centertext > a.button");
		if (! document.referrer) {
			backButton.text = "' . $txt['lp_all_categories'] . '";
			backButton.setAttribute("href", smf_scripturl + "?action=' . LP_ACTION . ';sa=categories");
		}', true);
	}
}
