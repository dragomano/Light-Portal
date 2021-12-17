<?php

declare(strict_types = 1);

/**
 * Page.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Lists\PageListInterface;

if (! defined('SMF'))
	die('Hacking attempt...');

final class Page
{
	public function show()
	{
		global $modSettings, $context, $txt, $scripturl;

		isAllowedTo('light_portal_view');

		$alias = Helper::request(LP_PAGE_PARAM);

		if (empty($alias) && ! empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] === 'chosen_page' && ! empty($modSettings['lp_frontpage_alias'])) {
			$context['lp_page'] = $this->getDataByAlias($modSettings['lp_frontpage_alias']);
		} else {
			$alias = explode(';', $alias)[0];

			if (Helper::isFrontpage($alias))
				redirectexit('action=' . LP_ACTION);

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

		Helper::parseContent($context['lp_page']['content'], $context['lp_page']['type']);

		if (empty($alias)) {
			$context['page_title']    = Helper::getTranslatedTitle($context['lp_page']['title']) ?: $txt['lp_portal'];
			$context['canonical_url'] = $scripturl;
			$context['linktree'][]    = array('name' => $txt['lp_portal']);
		} else {
			$context['page_title']    = Helper::getTranslatedTitle($context['lp_page']['title']) ?: $txt['lp_post_error_no_title'];
			$context['canonical_url'] = $scripturl . '?' . LP_PAGE_PARAM . '=' . $alias;

			if (! empty($context['lp_page']['category'])) {
				$context['linktree'][] = array(
					'name' => $context['lp_page']['category'],
					'url'  => $scripturl . '?action=' . LP_ACTION . ';sa=categories;id=' . $context['lp_page']['category_id']
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
			loadJavaScriptFile('light_portal/user.js', array('minimize' => true));
		}
	}

	public function getRelatedPages(): array
	{
		global $smcFunc, $modSettings, $context, $scripturl;

		if (empty($item = $context['lp_page']))
			return [];

		$title_words = explode(' ', Helper::getTranslatedTitle($item['title']));
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
				'permissions'  => Helper::getPermissions(),
				'current_page' => $item['id']
			)
		);

		$related_pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helper::isFrontpage($row['alias']))
				continue;

			Helper::parseContent($row['content'], $row['type']);

			$image = Helper::getImageFromText($row['content']);

			$related_pages[$row['page_id']] = array(
				'id'    => $row['page_id'],
				'title' => $row['title'],
				'alias' => $row['alias'],
				'link'  => $scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias'],
				'image' => $image ?: ($modSettings['lp_image_placeholder'] ?? '')
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $related_pages;
	}

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
			WHERE p.' . (! empty($params['alias']) ? 'alias = {string:alias}' : 'page_id = {int:item}'),
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
			if (! empty($modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				Helper::parseContent($content, $row['type']);
				$image_found = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

				if ($image_found && is_array($values)) {
					$all_images = array_pop($values);
					$image      = $modSettings['lp_page_og_image'] == 1 ? array_shift($all_images) : array_pop($all_images);
					$og_image   = $smcFunc['htmlspecialchars']($image);
				}
			}

			$data ??= array(
				'id'          => (int) $row['page_id'],
				'category_id' => (int) $row['category_id'],
				'author_id'   => (int) $row['author_id'],
				'author'      => $row['author_name'],
				'alias'       => $row['alias'],
				'description' => $row['description'],
				'content'     => $row['content'],
				'type'        => $row['type'],
				'permissions' => (int) $row['permissions'],
				'status'      => (int) $row['status'],
				'num_views'   => (int) $row['num_views'],
				'date'        => date('Y-m-d', (int) $row['created_at']),
				'time'        => date('H:i', (int) $row['created_at']),
				'created_at'  => (int) $row['created_at'],
				'updated_at'  => (int) $row['updated_at'],
				'image'       => $og_image
			);

			if (! empty($row['lang']))
				$data['title'][$row['lang']] = $row['title'];

			if (! empty($row['name']))
				$data['options'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $data ?? [];
	}

	public function getDataByAlias(string $alias): array
	{
		if (empty($alias))
			return [];

		$data = Helper::cache('page_' . $alias)->setFallback(__CLASS__, 'getData', array('alias' => $alias));

		$this->prepareData($data);

		return $data;
	}

	public function getDataByItem(int $item): array
	{
		if (empty($item))
			return [];

		$data = $this->getData(array('item' => $item));

		$this->prepareData($data);

		return $data;
	}

	public function showAsCards(PageListInterface $entity)
	{
		global $modSettings, $context;

		$start = Helper::request('start');
		$limit = (int) $modSettings['lp_num_items_per_page'] ?? 12;

		$total_items = $entity->getTotalCountPages();

		if ($start >= $total_items) {
			send_http_status(404);
			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = abs($start);

		$sort = (new FrontPage)->getOrderBy();

		$articles = $entity->getPages($start, $limit, $sort);

		$context['page_index'] = constructPageIndex($context['canonical_url'], Helper::request()->get('start'), $total_items, $limit);
		$context['start']      = Helper::request()->get('start');

		$context['lp_frontpage_articles']    = $articles;
		$context['lp_frontpage_num_columns'] = (new FrontPage)->getNumColumns();

		loadTemplate('LightPortal/ViewFrontPage');

		$context['sub_template']      = empty($modSettings['lp_frontpage_layout']) ? 'wrong_template' : 'show_' . $modSettings['lp_frontpage_layout'];
		$context['template_layers'][] = 'sorting';

		obExit();
	}

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
									: '" href="' . $scripturl . '?' . LP_PAGE_PARAM . '=' . $entry['alias']
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
						'function' => fn($entry) => $entry['views']['num'],
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

	public function fetchQueryResults(array &$items, array $row)
	{
		global $modSettings, $scripturl, $txt, $user_info;

		Helper::parseContent($row['content'], $row['type']);

		$image = null;
		if (! empty($modSettings['lp_show_images_in_articles'])) {
			$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
			$image = $first_post_image ? array_pop($value) : null;
		}

		if (empty($image) && ! empty($modSettings['lp_image_placeholder']))
			$image = $modSettings['lp_image_placeholder'];

		$items[$row['page_id']] = array(
			'id'        => $row['page_id'],
			'alias'     => $row['alias'],
			'author'    => array(
				'id'   => $author_id = (int) $row['author_id'],
				'link' => $scripturl . '?action=profile;u=' . $author_id,
				'name' => $row['author_name']
			),
			'date'      => Helper::getFriendlyTime((int) $row['date']),
			'datetime'  => date('Y-m-d', (int) $row['date']),
			'link'      => $scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias'],
			'views'     => array(
				'num'   => $row['num_views'],
				'title' => $txt['lp_views']
			),
			'replies'   => array(
				'num'   => ! empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'default' ? $row['num_comments'] : 0,
				'title' => $txt['lp_comments']
			),
			'title'     => $row['title'],
			'is_new'    => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
			'is_front'  => Helper::isFrontpage($row['alias']),
			'image'     => $image,
			'can_edit'  => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id']),
			'edit_link' => $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
		);

		$items[$row['page_id']]['msg_link'] = $items[$row['page_id']]['link'];

		$items[$row['page_id']]['author']['avatar'] = Helper::getUserAvatar($author_id)['href'];

		if (! empty($modSettings['lp_show_teaser']))
			$items[$row['page_id']]['teaser'] = Helper::getTeaser($row['description'] ?: $row['content']);
	}

	/**
	 * Change back button text and back button href
	 *
	 * Меняем текст и href кнопки «Назад»
	 */
	private function changeBackButton()
	{
		global $modSettings, $txt;

		addInlineJavaScript('
		const backButton = document.querySelector("#fatal_error + .centertext > a.button");
		if (! document.referrer) {
			backButton.setAttribute("href", smf_scripturl);
			backButton.text = "' . (empty($modSettings['lp_frontpage_mode']) ? $txt['lp_forum'] : $txt['lp_portal']) . '";
			if (document.location.href == smf_scripturl && backButton.text == "' . $txt['lp_portal'] . '") {
				backButton.setAttribute("href", smf_scripturl + "?action=forum");
				backButton.text = "' . $txt['lp_forum'] . '";
			}
		}', true);
	}

	private function setMeta()
	{
		global $context, $modSettings, $settings;

		if (empty($context['lp_page']))
			return;

		$context['meta_description'] = $context['lp_page']['description'];

		$keywords = [];
		if (! empty($context['lp_page']['tags'])) {
			$keywords = array_column($context['lp_page']['tags'], 'name');

			$modSettings['meta_keywords'] = implode(', ', $keywords);
		}

		$context['meta_tags'][] = array('prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'og:type', 'content' => 'article');
		$context['meta_tags'][] = array('prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'article:author', 'content' => $context['lp_page']['author']);
		$context['meta_tags'][] = array(
			'prefix'   => 'article: http://ogp.me/ns/article#',
			'property' => 'article:published_time',
			'content'  => date('Y-m-d\TH:i:s', (int) $context['lp_page']['created_at'])
		);

		if (! empty($context['lp_page']['updated_at']))
			$context['meta_tags'][] = array(
				'prefix'   => 'article: http://ogp.me/ns/article#',
				'property' => 'article:modified_time',
				'content'  => date('Y-m-d\TH:i:s', (int) $context['lp_page']['updated_at'])
			);

		if (! empty($context['lp_page']['category']))
			$context['meta_tags'][] = array('prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'article:section', 'content' => $context['lp_page']['category']);

		if (! empty($keywords)) {
			foreach ($keywords as $value) {
				$context['meta_tags'][] = array('prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'article:tag', 'content' => $value);
			}
		}

		if (! empty($modSettings['lp_page_og_image']) && ! empty($context['lp_page']['image']))
			$settings['og_image'] = $context['lp_page']['image'];
	}

	private function prepareRelatedPages()
	{
		global $context, $modSettings;

		if (empty($context['lp_page']['options']['show_related_pages']) || empty($modSettings['lp_show_related_pages']))
			return;

		$context['lp_page']['related_pages'] = $this->getRelatedPages();
	}

	private function prepareData(?array &$data)
	{
		global $user_info;

		if (empty($data))
			return;

		$is_author = ! empty($data['author_id']) && $data['author_id'] == $user_info['id'];

		$data['created']  = Helper::getFriendlyTime((int) $data['created_at']);
		$data['updated']  = Helper::getFriendlyTime((int) $data['updated_at']);
		$data['can_view'] = Helper::canViewItem($data['permissions']) || $user_info['is_admin'] || $is_author;
		$data['can_edit'] = $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $is_author);

		if ($data['type'] === 'bbc') {
			Helper::require('Subs-Post');
			$data['content'] = un_preparsecode($data['content']);
		}

		if (! empty($data['category_id']))
			$data['category'] = Helper::getAllCategories()[$data['category_id']]['name'];

		if (! empty($data['options']['keywords']))
			$data['tags'] = $this->getTags($data['options']['keywords']);

		$data['addons'] = '';

		Addon::run('preparePageData', array(&$data, $is_author));
	}

	private function prepareComments()
	{
		global $modSettings, $context;

		if (empty($modSettings['lp_show_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
			return;

		if ($modSettings['lp_show_comment_block'] == 'none')
			return;

		loadLanguage('Editor');

		Addon::run('comments');

		if (! empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block']))
			return;

		(new Comment($context['lp_page']['alias']))->prepare();
	}

	private function getTags(string $tags): array
	{
		global $smcFunc, $scripturl;

		$request = $smcFunc['db_query']('', '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			WHERE FIND_IN_SET(tag_id, {string:tags})
			ORDER BY value',
			array(
				'tags' => $tags
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['tag_id']] = array(
				'name' => $row['value'],
				'href' => $scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $row['tag_id']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	private function updateNumViews()
	{
		global $context, $user_info, $smcFunc;

		if (empty($context['lp_page']['id']) || $user_info['possibly_robot'])
			return;

		if (Helper::session()->isEmpty('light_portal_last_page_viewed') || Helper::session()->get('light_portal_last_page_viewed') != $context['lp_page']['id']) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}',
				array(
					'item' => $context['lp_page']['id']
				)
			);

			$smcFunc['lp_num_queries']++;

			Helper::session()->put('light_portal_last_page_viewed', $context['lp_page']['id']);
		}
	}
}
