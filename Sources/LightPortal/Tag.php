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
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Tag
{
	/**
	 * Display all portal pages by specified tag
	 *
	 * Отображение всех страниц портала с указанным тегом
	 *
	 * @return void
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
			$this->showAsArticles();

		$listOptions = array(
			'id' => 'lp_tags',
			'items_per_page' => $modSettings['defaultMaxListItems'] ?: 50,
			'title' => $context['page_title'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'date',
			'get_items' => array(
				'function' => array($this, 'getPages')
			),
			'get_count' => array(
				'function' => array($this, 'getTotalCountPages')
			),
			'columns' => array(
				'date' => array(
					'header' => array(
						'value' => $txt['date']
					),
					'data' => array(
						'db'    => 'date',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.created_at DESC, p.updated_at DESC',
						'reverse' => 'p.created_at, p.updated_at'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							return '<a class="bbc_link' . (
								$entry['is_front']
									? ' new_posts" href="' . $scripturl
									: '" href="' . $scripturl . '?page=' . $entry['alias']
							) . '">' . $entry['title'] . '</a>';
						},
						'class' => 'word_break'
					),
					'sort' => array(
						'default' => 't.title DESC',
						'reverse' => 't.title'
					)
				),
				'author' => array(
					'header' => array(
						'value' => $txt['author']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							if (empty($entry['author_id']))
								return $entry['author_name'];

							return '<a href="' . $scripturl . '?action=profile;u=' . $entry['author_id'] . '">' . $entry['author_name'] . '</a>';
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'author_name DESC',
						'reverse' => 'author_name'
					)
				),
				'num_views' => array(
					'header' => array(
						'value' => $txt['views']
					),
					'data' => array(
						'db'    => 'num_views',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views'
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
	 */
	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		global $smcFunc, $txt, $user_info, $context, $modSettings, $scripturl, $memberContext;

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
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			Helpers::parseContent($row['content'], $row['type']);

			$image = null;
			if (!empty($modSettings['lp_show_images_in_articles'])) {
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
				$image = $first_post_image ? array_pop($value) : null;
			}

			if (empty($image) && !empty($modSettings['lp_image_placeholder']))
				$image = $modSettings['lp_image_placeholder'];

			$items[$row['page_id']] = array(
				'id'        => $row['page_id'],
				'author'    => array(
					'id'   => $author_id = $row['author_id'],
					'link' => $scripturl . '?action=profile;u=' . $author_id,
					'name' => $row['author_name']
				),
				'date'      => Helpers::getFriendlyTime($row['date']),
				'datetime'  => date('Y-m-d', $row['date']),
				'link'      => $scripturl . '?page=' . $row['alias'],
				'views'     => array(
					'num'   => $row['num_views'],
					'title' => $txt['lp_views']
				),
				'replies'   => array(
					'num'   => !empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'default' ? $row['num_comments'] : 0,
					'title' => $txt['lp_comments']
				),
				'title'     => $row['title'],
				'is_new'    => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
				'is_front'  => Helpers::isFrontpage($row['alias']),
				'image'     => $image,
				'can_edit'  => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id']),
				'edit_link' => $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
			);

			$items[$row['page_id']]['msg_link'] = $items[$row['page_id']]['link'];

			loadMemberData($author_id);

			$items[$row['page_id']]['author']['avatar'] = $modSettings['avatar_url'] . '/default.png';
			if (loadMemberContext($author_id, true)) {
				$items[$row['page_id']]['author']['avatar'] = $memberContext[$author_id]['avatar']['href'];
			}

			if (!empty($modSettings['lp_show_teaser']))
				$items[$row['page_id']]['teaser'] = Helpers::getTeaser($row['description'] ?: $row['content']);

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
	 * Show tags as page articles
	 *
	 * Отображаем теги в виде карточек статей
	 *
	 * @return void
	 */
	private function showAsArticles()
	{
		global $modSettings, $context;

		$start = abs(Helpers::request('start'));
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$total_items = $this->getTotalCountPages();

		if ($start >= $total_items) {
			send_http_status(404);
			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$sort = (new FrontPage)->getOrderBy();

		$articles = $this->getPages($start, $limit, $sort);

		$context['page_index'] = constructPageIndex($context['canonical_url'], Helpers::request()->get('start'), $total_items, $limit);
		$context['start']      = Helpers::request()->get('start');

		$context['lp_frontpage_articles']    = $articles;
		$context['lp_frontpage_num_columns'] = (new FrontPage)->getNumColumns();

		loadTemplate('LightPortal/ViewFrontPage');

		$context['sub_template']      = 'show_articles';
		$context['template_layers'][] = 'sorting';

		obExit();
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
