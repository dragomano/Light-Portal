<?php

namespace Bugo\LightPortal;

/**
 * Tag.php
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

class Tag implements PageListInterface
{
	/**
	 * Display all portal pages by specified tag
	 *
	 * Отображение всех страниц портала с указанным тегом
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function show()
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['lp_tag'] = Helpers::request('id', 0);

		if (empty($context['lp_tag']))
			$this->showAll();

		if (array_key_exists($context['lp_tag'], Helpers::getAllTags()) === false) {
			$this->changeBackButton();
			fatal_lang_error('lp_tag_not_found', false, null, 404);
		}

		$context['page_title']     = sprintf($txt['lp_all_tags_by_key'], Helpers::getAllTags()[$context['lp_tag']]);
		$context['canonical_url']  = $scripturl . '?action=portal;sa=tags;id=' . $context['lp_tag'];
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $txt['lp_all_page_tags'],
			'url'  => $scripturl . '?action=portal;sa=tags'
		);

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		if (!empty($modSettings['lp_show_items_as_articles']))
			(new Page)->showAsCards($this);

		$listOptions = (new Page)->getList();
		$listOptions['id'] = 'lp_tags';
		$listOptions['get_items'] = array(
			'function' => array($this, 'getPages')
		);
		$listOptions['get_count'] = array(
			'function' => array($this, 'getTotalCountPages')
		);

		Helpers::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'lp_tags';

		obExit();
	}

	/**
	 * Get the list of pages with selected tag
	 *
	 * Получаем список страниц с указанным тегом
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @return array
	 * @throws \Exception
	 */
	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		global $smcFunc, $txt, $user_info, $context, $scripturl;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, {string:guest}) AS author_name, ps.value, t.title
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
			array(
				'guest'        => $txt['guest_title'],
				'lang'         => $user_info['language'],
				'id'           => $context['lp_tag'],
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			)
		);

		$items = [];
		$page  = new Page;
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$page->fetchQueryResults($items, $row);

			if (!empty($row['category_id'])) {
				$items[$row['page_id']]['section'] = array(
					'name' => Helpers::getAllCategories()[$row['category_id']]['name'],
					'link' => $scripturl . '?action=portal;sa=categories;id=' . $row['category_id']
				);
			}
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * Get the total number of pages with selected tag
	 *
	 * Подсчитываем общее количество страниц с указанным тегом
	 *
	 * @return int
	 */
	public function getTotalCountPages(): int
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
			WHERE FIND_IN_SET({int:id}, ps.value) > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			array(
				'id'           => $context['lp_tag'],
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions()
			)
		);

		[$num_items] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $num_items;
	}

	/**
	 * Display all tags at once
	 *
	 * Отображение всех тегов сразу
	 *
	 * @return void
	 */
	public function showAll()
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['page_title']     = $txt['lp_all_page_tags'];
		$context['canonical_url']  = $scripturl . '?action=portal;sa=tags';
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$listOptions = array(
			'id' => 'tags',
			'items_per_page' => $modSettings['defaultMaxListItems'] ?: 50,
			'title' => $context['page_title'],
			'no_items_label' => $txt['lp_no_tags'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'value',
			'get_items' => array(
				'function' => array($this, 'getAll')
			),
			'get_count' => array(
				'function' => array($this, 'getTotalCount')
			),
			'columns' => array(
				'value' => array(
					'header' => array(
						'value' => $txt['lp_keyword_column']
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<a href="' . $entry['link'] . '">' . $entry['value'] . '</a>';
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 't.value DESC',
						'reverse' => 't.value'
					)
				),
				'frequency' => array(
					'header' => array(
						'value' => $txt['lp_frequency_column']
					),
					'data' => array(
						'db'    => 'frequency',
						'class' => 'centertext'
					)
				)
			),
			'form' => array(
				'href' => $context['canonical_url']
			)
		);

		Helpers::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'tags';

		obExit();
	}

	/**
	 * Get the list of all tags
	 *
	 * Получаем список всех тегов
	 *
	 * @return array
	 */
	public function getList(): array
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			ORDER BY value',
			array()
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['tag_id']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * Get the list of all tags
	 *
	 * Получаем список всех тегов
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @return array
	 */
	public function getAll(int $start, int $items_per_page, string $sort): array
	{
		global $smcFunc, $scripturl;

		$request = $smcFunc['db_query']('', '
			SELECT t.tag_id, t.value
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
				INNER JOIN {db_prefix}lp_tags AS t ON (FIND_IN_SET(t.tag_id, ps.value) > 0)
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}' . ($items_per_page ? '
			LIMIT {int:start}, {int:limit}' : ''),
			array(
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			)
		);

		$items = [];
		$i = 1;

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			!isset($items[$row['tag_id']])
				? $i = 1
				: $i++;

			$items[$row['tag_id']] = array(
				'value'     => $row['value'],
				'link'      => $scripturl . '?action=portal;sa=tags;id=' . $row['tag_id'],
				'frequency' => $i
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		uasort($items, function ($a, $b) {
			return $a['frequency'] < $b['frequency'];
		});

		return $items;
	}

	/**
	 * Get the total number of pages with tags
	 *
	 * Подсчитываем общее количество страниц с тегами
	 *
	 * @return int
	 */
	public function getTotalCount(): int
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			array(
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions()
			)
		);

		[$num_items] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $num_items;
	}

	/**
	 * Change back button text and back button href
	 *
	 * Меняем текст и href кнопки «Назад»
	 *
	 * @return void
	 */
	private function changeBackButton()
	{
		global $txt;

		addInlineJavaScript('
		const backButton = document.querySelector("#fatal_error + .centertext > a.button");
		if (!document.referrer) {
			backButton.text = "' . $txt['lp_all_page_tags'] . '";
			backButton.setAttribute("href", smf_scripturl + "?action=portal;sa=tags");
		}', true);
	}
}
