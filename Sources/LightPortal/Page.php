<?php

namespace Bugo\LightPortal;

use Exception;

/**
 * Page.php
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

class Page
{
	public const STATUS_ACTIVE = 1;
	public const STATUS_INACTIVE = 0;

	/**
	 * Display the page by its alias
	 *
	 * Просматриваем страницу по её алиасу
	 *
	 * @return void
	 * @throws Exception
	 */
	public function show()
	{
		global $modSettings, $context, $txt, $scripturl;

		isAllowedTo('light_portal_view');

		$alias = Helpers::request('page');

		if (empty($alias) && !empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] == 'chosen_page' && !empty($modSettings['lp_frontpage_alias'])) {
			$context['lp_page'] = $this->getDataByAlias($modSettings['lp_frontpage_alias']);
		} else {
			$alias = explode(';', $alias)[0];
			$context['lp_page'] = $this->getDataByAlias($alias);
		}

		if (empty($context['lp_page'])) {
			$this->changeBackButton();
			fatal_lang_error('lp_page_not_found', false, null, 404);
		}

		if (empty($context['lp_page']['can_view'])) {
			$this->changeBackButton();
			fatal_lang_error('cannot_light_portal_view_page', false);
		}

		if (empty($context['lp_page']['status']) && empty($context['lp_page']['can_edit'])) {
			$this->changeBackButton();
			fatal_lang_error('lp_page_not_activated', false);
		}

		if ($context['lp_page']['created_at'] > time())
			send_http_status(404);

		$context['lp_page']['errors'] = [];
		if (empty($context['lp_page']['status']) && $context['lp_page']['can_edit'])
			$context['lp_page']['errors'][] = $txt['lp_page_visible_but_disabled'];

		Helpers::parseContent($context['lp_page']['content'], $context['lp_page']['type']);

		if (empty($alias)) {
			$context['page_title']          = Helpers::getTitle($context['lp_page']) ?: $txt['lp_portal'];
			$context['canonical_url']       = $scripturl;
			$context['lp_current_page_url'] = $context['canonical_url'] . '?';
			$context['linktree'][] = array(
				'name' => $txt['lp_portal']
			);
		} else {
			$context['page_title']          = Helpers::getTitle($context['lp_page']) ?: $txt['lp_post_error_no_title'];
			$context['canonical_url']       = $scripturl . '?page=' . $alias;
			$context['lp_current_page_url'] = $context['canonical_url'] . ';';

			if (!empty($context['lp_page']['category'])) {
				$context['linktree'][] = array(
					'name' => $context['lp_page']['category'],
					'url'  => $scripturl . '?action=portal;sa=categories;id=' . $context['lp_page']['category_id']
				);
			}

			$context['linktree'][] = array(
				'name' => $context['page_title']
			);
		}

		loadTemplate('LightPortal/ViewPage');
		$context['sub_template'] = 'show_page';

		$this->setMeta();
		$this->prepareRelatedPages();
		$this->prepareComments();
		$this->updateNumViews();

		if ($context['user']['is_logged']) {
			loadJavaScriptFile('https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2/dist/alpine.min.js', array('external' => true, 'defer' => true));
			loadJavaScriptFile('light_portal/view_page.js', array('minimize' => true));
		}
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
		global $modSettings, $txt;

		addInlineJavaScript('
		const backButton = document.querySelector("#fatal_error + .centertext > a.button");
		if (!document.referrer) {
			backButton.setAttribute("href", smf_scripturl);
			backButton.text = "' . (!empty($modSettings['lp_frontpage_mode']) ? $txt['lp_portal'] : $txt['lp_forum']) . '";
			if (document.location.href == smf_scripturl && backButton.text == "' . $txt['lp_portal'] . '") {
				backButton.setAttribute("href", smf_scripturl + "?action=forum");
				backButton.text = "' . $txt['lp_forum'] . '";
			}
		}', true);
	}

	/**
	 * Creating meta data for SEO
	 *
	 * Формируем мета-данные для SEO
	 *
	 * @return void
	 */
	private function setMeta()
	{
		global $context, $modSettings, $settings;

		if (empty($context['lp_page']))
			return;

		$modSettings['meta_keywords'] = implode(', ', $context['lp_page']['keywords']);
		$context['meta_description']  = $context['lp_page']['description'];

		$context['optimus_og_type']['article'] = array(
			'published_time' => date('Y-m-d\TH:i:s', $context['lp_page']['created_at']),
			'modified_time'  => !empty($context['lp_page']['updated_at']) ? date('Y-m-d\TH:i:s', $context['lp_page']['updated_at']) : null,
			'author'         => $context['lp_page']['author']
		);

		if (!empty($context['lp_page']['category']))
			$context['optimus_og_type']['article']['section'] = $context['lp_page']['category'];

		if (!empty($modSettings['lp_page_og_image']) && !empty($context['lp_page']['image']))
			$settings['og_image'] = $context['lp_page']['image'];
	}

	/**
	 * @return void
	 */
	private function prepareRelatedPages()
	{
		global $context, $modSettings;

		if (empty($context['lp_page']['options']['show_related_pages']) || empty($modSettings['lp_show_related_pages']))
			return;

		$context['lp_page']['related_pages'] = $this->getRelatedPages();
	}

	/**
	 * @return array
	 */
	public function getRelatedPages(): array
	{
		global $smcFunc, $modSettings, $context;

		if (empty($item = $context['lp_page']))
			return [];

		$title_words = explode(' ', $title = Helpers::getTitle($item));
		$alias_words = explode('_', $item['alias']);

		$search_formula = '';
		foreach ($title_words as $key => $word) {
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 2 . ' ELSE 0 END';
		}

		foreach ($alias_words as $key => $word) {
			$search_formula .= ' + CASE WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\') THEN ' . (count($alias_words) - $key) . ' ELSE 0 END';
		}

		$request = $smcFunc['db_query']('', '
			SELECT p.page_id, p.alias, p.content, p.type, (' . $search_formula . ') AS related, t.title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.lang = {string:current_lang})
			WHERE (' . $search_formula . ') > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND p.page_id != {int:current_page}
			ORDER BY related DESC
			LIMIT 4',
			array(
				'current_lang' => $context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'current_page' => $item['id']
			)
		);

		$related_pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::isFrontpage($row['alias']))
				continue;

			Helpers::parseContent($row['content'], $row['type']);
			$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
			$image = !empty($first_post_image) ? array_pop($value) : null;
			if (empty($image) && !empty($modSettings['lp_image_placeholder']))
				$image = $modSettings['lp_image_placeholder'];

			$related_pages[$row['page_id']] = array(
				'id'    => $row['page_id'],
				'title' => $row['title'],
				'alias' => $row['alias'],
				'image' => $image
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $related_pages;
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	private function prepareComments()
	{
		global $modSettings, $context;

		if (empty($modSettings['lp_show_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
			return;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'none')
			return;

		Subs::runAddons('comments');

		if (!empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block']))
			return;

		(new Comment($context['lp_page']['alias']))->prepare();
	}

	/**
	 * Get the page data from lp_pages table
	 *
	 * Получаем данные страницы из таблицы в базе данных
	 *
	 * @param array $params
	 * @return array
	 */
	public function getData(array $params): array
	{
		global $smcFunc, $txt, $modSettings;

		if (empty($params))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions, p.status, p.num_views, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, pt.lang, pt.title, pp.name, pp.value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
			WHERE p.' . (!empty($params['alias']) ? 'alias = {string:alias}' : 'page_id = {int:item}'),
			array_merge(
				$params,
				array(
					'guest' => $txt['guest_title']
				)
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			$og_image = null;
			if (!empty($modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				Helpers::parseContent($content, $row['type']);
				$image_found = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

				if ($image_found && is_array($values)) {
					$all_images = array_pop($values);
					$image      = $modSettings['lp_page_og_image'] == 1 ? array_shift($all_images) : array_pop($all_images);
					$og_image   = $smcFunc['htmlspecialchars']($image);
				}
			}

			if (!isset($data))
				$data = array(
					'id'          => $row['page_id'],
					'category_id' => $row['category_id'],
					'author_id'   => $row['author_id'],
					'author'      => $row['author_name'],
					'alias'       => $row['alias'],
					'description' => $row['description'],
					'content'     => $row['content'],
					'type'        => $row['type'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'num_views'   => $row['num_views'],
					'date'        => date('Y-m-d', $row['created_at']),
					'time'        => date('H:i', $row['created_at']),
					'created_at'  => $row['created_at'],
					'updated_at'  => $row['updated_at'],
					'image'       => $og_image,
					'keywords'    => []
				);

			if (!empty($row['lang']))
				$data['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$data['options'][$row['name']] = $row['value'];
		}

		if (!empty($data['category_id']))
			$data['category'] = Helpers::getAllCategories()[$data['category_id']]['name'];

		if (!empty($data['options']['keywords'])) {
			$keywords = explode(',', $data['options']['keywords']);
			foreach ($keywords as $key) {
				$data['keywords'][$key] = Helpers::getAllTags()[$key];
			}
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * @param string $alias
	 * @return array
	 */
	public function getDataByAlias(string $alias): array
	{
		if (empty($alias))
			return [];

		$data = Helpers::cache('page_' . $alias, 'getData', __CLASS__, LP_CACHE_TIME, array('alias' => $alias));

		$this->prepareData($data);

		return $data;
	}

	/**
	 * @param int $item
	 * @return array
	 */
	public function getDataByItem(int $item): array
	{
		if (empty($item))
			return [];

		$data = $this->getData(array('item' => $item));

		$this->prepareData($data);

		return $data;
	}

	/**
	 * Show pages as articles
	 *
	 * Отображаем страницы как карточки
	 *
	 * @param PageListInterface $entity
	 * @return void
	 */
	public function showAsCards(PageListInterface $entity)
	{
		global $modSettings, $context;

		$start = Helpers::request('start');
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$total_items = $entity->getTotalCountPages();

		if ($start >= $total_items) {
			send_http_status(404);
			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = abs($start);

		$sort = (new FrontPage)->getOrderBy();

		$articles = $entity->getPages($start, $limit, $sort);

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
	 * Get page list within selected category, with common tag, etc.
	 *
	 * Получаем список страниц внутри заданной категории, с общим тегом и т. д.
	 *
	 * @return array
	 */
	public function getList(): array
	{
		global $modSettings, $context, $txt, $scripturl;

		return array(
			'items_per_page' => $modSettings['defaultMaxListItems'] ?: 50,
			'title' => $context['page_title'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'date',
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
							if (empty($entry['author']['id']))
								return $entry['author']['name'];

							return '<a href="' . $scripturl . '?action=profile;u=' . $entry['author']['id'] . '">' . $entry['author']['name'] . '</a>';
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
						'function' => function ($entry)
						{
							return $entry['views']['num'];
						},
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
	}

	/**
	 * @param array $items
	 * @param array $row
	 * @return void
	 */
	public function fetchQueryResults(array &$items, array $row)
	{
		global $modSettings, $scripturl, $txt, $user_info, $memberContext;

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
			'alias'     => $row['alias'],
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
	}

	/**
	 * Additional processing of page data
	 *
	 * Дополнительная обработка данных страницы
	 *
	 * @param array|null $data
	 * @return void
	 */
	private function prepareData(?array &$data)
	{
		global $user_info, $modSettings;

		if (empty($data))
			return;

		$is_author = !empty($data['author_id']) && $data['author_id'] == $user_info['id'];

		$data['created']  = Helpers::getFriendlyTime($data['created_at']);
		$data['updated']  = Helpers::getFriendlyTime($data['updated_at']);
		$data['can_view'] = Helpers::canViewItem($data['permissions']) || $user_info['is_admin'] || $is_author;
		$data['can_edit'] = $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $is_author);

		if (!empty($modSettings['enable_likes'])) {
			$user_likes = $user_info['is_guest'] ? [] : $this->prepareLikesContext($data['id']);

			$data['likes'] = array(
				'count'    => $this->getLikesCount($data['id']),
				'you'      => in_array($data['id'], $user_likes),
				'can_like' => !$user_info['is_guest'] && !$is_author && allowedTo('likes_like')
			);
		}
	}

	/**
	 * Get an array of "likes" info for the $page and the current user
	 *
	 * Получаем массив лайков для страницы $page и текущего пользователя
	 *
	 * @param int $page
	 * @return array
	 */
	private function prepareLikesContext(int $page): array
	{
		global $user_info, $smcFunc;

		if (empty($page))
			return [];

		$cache_key = 'likes_page_' . $page . '_' . $user_info['id'];

		if (($liked_pages = Helpers::cache()->get($cache_key)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT content_id
				FROM {db_prefix}user_likes AS l
					INNER JOIN {db_prefix}lp_pages AS p ON (l.content_id = p.page_id)
				WHERE l.id_member = {int:current_user}
					AND l.content_type = {literal:lpp}
					AND p.page_id = {int:page}',
				array(
					'current_user' => $user_info['id'],
					'page'         => $page
				)
			);

			$liked_pages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$liked_pages[] = (int) $row['content_id'];

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put($cache_key, $liked_pages);
		}

		return $liked_pages;
	}

	/**
	 * Get number of likes for the $page
	 *
	 * Получаем количество лайков для страницы $page
	 *
	 * @param int $page
	 * @return int
	 */
	private function getLikesCount(int $page): int
	{
		global $smcFunc;

		if (empty($page))
			return 0;

		$cache_key = 'likes_page_' . $page . '_count';

		if (($num_likes = Helpers::cache()->get($cache_key)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(content_id)
				FROM {db_prefix}user_likes AS l
					INNER JOIN {db_prefix}lp_pages AS p ON (l.content_id = p.page_id)
				WHERE l.content_type = {literal:lpp}
					AND p.page_id = {int:page}',
				array(
					'page' => $page
				)
			);

			[$num_likes] = $smcFunc['db_fetch_row']($request);

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put($cache_key, $num_likes);
		}

		return (int) $num_likes;
	}

	/**
	 * @return void
	 */
	private function updateNumViews()
	{
		global $context, $user_info, $smcFunc;

		if (empty($context['lp_page']['id']) || $user_info['possibly_robot'])
			return;

		if (Helpers::session()->isEmpty('light_portal_last_page_viewed') || Helpers::session('light_portal_last_page_viewed') != $context['lp_page']['id']) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}',
				array(
					'item' => $context['lp_page']['id']
				)
			);

			$smcFunc['lp_num_queries']++;

			Helpers::session()->put('light_portal_last_page_viewed', $context['lp_page']['id']);
		}
	}
}
