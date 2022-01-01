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

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Lists\PageListInterface;

if (! defined('SMF'))
	die('No direct access...');

final class Page
{
	use Helper;

	public function show()
	{
		isAllowedTo('light_portal_view');

		$alias = $this->request(LP_PAGE_PARAM);

		if (empty($alias) && ! empty($this->modSettings['lp_frontpage_mode']) && $this->modSettings['lp_frontpage_mode'] === 'chosen_page' && ! empty($this->modSettings['lp_frontpage_alias'])) {
			$this->context['lp_page'] = $this->getDataByAlias($this->modSettings['lp_frontpage_alias']);
		} else {
			$alias = explode(';', $alias)[0];

			if ($this->isFrontpage($alias))
				redirectexit('action=' . LP_ACTION);

			$this->context['lp_page'] = $this->getDataByAlias($alias);
		}

		if (empty($this->context['lp_page'])) {
			$this->changeErrorPage();
			fatal_lang_error('lp_page_not_found', false, null, 404);
		}

		if (empty($this->context['lp_page']['can_view'])) {
			$this->changeErrorPage();
			fatal_lang_error('cannot_light_portal_view_page', false);
		}

		if (empty($this->context['lp_page']['status']) && empty($this->context['lp_page']['can_edit'])) {
			$this->changeErrorPage();
			fatal_lang_error('lp_page_not_activated', false);
		}

		if ($this->context['lp_page']['created_at'] > time())
			send_http_status(404);

		$this->context['lp_page']['errors'] = [];
		if (empty($this->context['lp_page']['status']) && $this->context['lp_page']['can_edit'])
			$this->context['lp_page']['errors'][] = $this->txt['lp_page_visible_but_disabled'];

		$this->context['lp_page']['content'] = parse_content($this->context['lp_page']['content'], $this->context['lp_page']['type']);

		if (empty($alias)) {
			$this->context['page_title']    = $this->getTranslatedTitle($this->context['lp_page']['title']) ?: $this->txt['lp_portal'];
			$this->context['canonical_url'] = $this->scripturl;
			$this->context['linktree'][]    = ['name' => $this->txt['lp_portal']];
		} else {
			$this->context['page_title']    = $this->getTranslatedTitle($this->context['lp_page']['title']) ?: $this->txt['lp_post_error_no_title'];
			$this->context['canonical_url'] = $this->scripturl . '?' . LP_PAGE_PARAM . '=' . $alias;

			if (! empty($this->context['lp_page']['category'])) {
				$this->context['linktree'][] = [
					'name' => $this->context['lp_page']['category'],
					'url'  => $this->scripturl . '?action=' . LP_ACTION . ';sa=categories;id=' . $this->context['lp_page']['category_id']
				];
			}

			$this->context['linktree'][] = [
				'name' => $this->context['page_title']
			];
		}

		loadTemplate('LightPortal/ViewPage');

		$this->context['sub_template'] = 'show_page';

		$this->setMeta();
		$this->prepareRelatedPages();
		$this->prepareComments();
		$this->updateNumViews();

		if ($this->context['user']['is_logged']) {
			loadJavaScriptFile('https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2/dist/alpine.min.js', ['external' => true, 'defer' => true]);
			loadJavaScriptFile('light_portal/user.js', ['minimize' => true]);
		}
	}

	public function getRelatedPages(): array
	{
		if (empty($item = $this->context['lp_page']))
			return [];

		$title_words = explode(' ', $this->getTranslatedTitle($item['title']));
		$alias_words = explode('_', $item['alias']);

		$search_formula = '';
		foreach ($title_words as $key => $word) {
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 2 . ' ELSE 0 END';
		}

		foreach ($alias_words as $key => $word) {
			$search_formula .= ' + CASE WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\') THEN ' . (count($alias_words) - $key) . ' ELSE 0 END';
		}

		$request = $this->smcFunc['db_query']('', '
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
			[
				'current_lang' => $this->context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'current_page' => $item['id']
			]
		);

		$related_pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$row['content'] = parse_content($row['content'], $row['type']);

			$image = $this->getImageFromText($row['content']);

			$related_pages[$row['page_id']] = [
				'id'    => $row['page_id'],
				'title' => $row['title'],
				'alias' => $row['alias'],
				'link'  => $this->scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias'],
				'image' => $image ?: ($this->modSettings['lp_image_placeholder'] ?? '')
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $related_pages;
	}

	public function getData(array $params): array
	{
		if (empty($params))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions, p.status, p.num_views, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, pt.lang, pt.title, pp.name, pp.value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
			WHERE p.' . (empty($params['alias']) ? 'page_id = {int:item}' : 'alias = {string:alias}'),
			array_merge(
				$params,
				[
					'guest' => $this->txt['guest_title']
				]
			)
		);

		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			$og_image = null;
			if (! empty($this->modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				$content = parse_content($content, $row['type']);
				$image_found = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

				if ($image_found && is_array($values)) {
					$all_images = array_pop($values);
					$image      = $this->modSettings['lp_page_og_image'] == 1 ? array_shift($all_images) : array_pop($all_images);
					$og_image   = $this->smcFunc['htmlspecialchars']($image);
				}
			}

			$data ??= [
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
				'created_at'  => (int) $row['created_at'],
				'updated_at'  => (int) $row['updated_at'],
				'image'       => $og_image
			];

			if (! empty($row['lang']))
				$data['title'][$row['lang']] = $row['title'];

			if (! empty($row['name']))
				$data['options'][$row['name']] = $row['value'];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $data ?? [];
	}

	public function getDataByAlias(string $alias): array
	{
		if (empty($alias))
			return [];

		$data = $this->cache('page_' . $alias)->setFallback(__CLASS__, 'getData', ['alias' => $alias]);

		$this->prepareData($data);

		return $data;
	}

	public function getDataByItem(int $item): array
	{
		if (empty($item))
			return [];

		$data = $this->getData(['item' => $item]);

		$this->prepareData($data);

		return $data;
	}

	public function showAsCards(PageListInterface $entity)
	{
		$start = $this->request('start');
		$limit = (int) $this->modSettings['lp_num_items_per_page'] ?? 12;

		$total_items = $entity->getTotalCountPages();

		if ($start >= $total_items) {
			send_http_status(404);
			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = abs($start);

		$sort = (new FrontPage)->getOrderBy();

		$articles = $entity->getPages($start, $limit, $sort);

		$this->context['page_index'] = constructPageIndex($this->context['canonical_url'], $this->request()->get('start'), $total_items, $limit);
		$this->context['start']      = $this->request()->get('start');

		$this->context['lp_frontpage_articles']    = $articles;
		$this->context['lp_frontpage_num_columns'] = (new FrontPage)->getNumColumns();

		loadTemplate('LightPortal/ViewFrontPage');

		$this->context['sub_template']      = empty($this->modSettings['lp_frontpage_layout']) ? 'wrong_template' : 'show_' . $this->modSettings['lp_frontpage_layout'];
		$this->context['template_layers'][] = 'sorting';

		obExit();
	}

	public function getList(): array
	{
		return [
			'items_per_page' => $this->modSettings['defaultMaxListItems'] ?: 50,
			'title' => $this->context['page_title'],
			'no_items_label' => $this->txt['lp_no_items'],
			'base_href' => $this->context['canonical_url'],
			'default_sort_col' => 'date',
			'columns' => [
				'date' => [
					'header' => [
						'value' => $this->txt['date']
					],
					'data' => [
						'db'    => 'date',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'p.created_at DESC, p.updated_at DESC',
						'reverse' => 'p.created_at, p.updated_at'
					]
				],
				'title' => [
					'header' => [
						'value' => $this->txt['lp_title']
					],
					'data' => [
						'function' => fn($entry) => '<a class="bbc_link' . (
							$entry['is_front']
								? ' new_posts" href="' . $this->scripturl
								: '" href="' . $this->scripturl . '?' . LP_PAGE_PARAM . '=' . $entry['alias']
						) . '">' . $entry['title'] . '</a>',
						'class' => 'word_break'
					],
					'sort' => [
						'default' => 't.title DESC',
						'reverse' => 't.title'
					]
				],
				'author' => [
					'header' => [
						'value' => $this->txt['author']
					],
					'data' => [
						'function' => fn($entry) => empty($entry['author']['id']) ? $entry['author']['name'] : '<a href="' . $this->scripturl . '?action=profile;u=' . $entry['author']['id'] . '">' . $entry['author']['name'] . '</a>',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'author_name DESC',
						'reverse' => 'author_name'
					]
				],
				'num_views' => [
					'header' => [
						'value' => $this->txt['views']
					],
					'data' => [
						'function' => fn($entry) => $entry['views']['num'],
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views'
					]
				]
			],
			'form' => [
				'href' => $this->context['canonical_url']
			]
		];
	}

	public function fetchQueryResults(array &$items, array $row)
	{
		$row['content'] = parse_content($row['content'], $row['type']);

		$image = null;
		if (! empty($this->modSettings['lp_show_images_in_articles'])) {
			$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
			$image = $first_post_image ? array_pop($value) : null;
		}

		if (empty($image) && ! empty($this->modSettings['lp_image_placeholder']))
			$image = $this->modSettings['lp_image_placeholder'];

		$items[$row['page_id']] = [
			'id'        => $row['page_id'],
			'alias'     => $row['alias'],
			'author'    => [
				'id'   => $author_id = (int) $row['author_id'],
				'link' => $this->scripturl . '?action=profile;u=' . $author_id,
				'name' => $row['author_name']
			],
			'date'      => $this->getFriendlyTime((int) $row['date']),
			'datetime'  => date('Y-m-d', (int) $row['date']),
			'link'      => $this->scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias'],
			'views'     => [
				'num'   => $row['num_views'],
				'title' => $this->txt['lp_views']
			],
			'replies'   => [
				'num'   => ! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] == 'default' ? $row['num_comments'] : 0,
				'title' => $this->txt['lp_comments']
			],
			'title'     => $row['title'],
			'is_new'    => $this->user_info['last_login'] < $row['date'] && $row['author_id'] != $this->user_info['id'],
			'is_front'  => $this->isFrontpage($row['alias']),
			'image'     => $image,
			'can_edit'  => $this->user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $this->user_info['id']),
			'edit_link' => $this->scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
		];

		$items[$row['page_id']]['msg_link'] = $items[$row['page_id']]['link'];

		$items[$row['page_id']]['author']['avatar'] = $this->getUserAvatar($author_id)['href'] ?? '';

		if (! empty($this->modSettings['lp_show_teaser']))
			$items[$row['page_id']]['teaser'] = $this->getTeaser($row['description'] ?: $row['content']);
	}

	private function changeErrorPage()
	{
		$this->context['error_link'] = $this->scripturl;
		$this->txt['back'] = empty($this->modSettings['lp_frontpage_mode']) ? $this->txt['lp_forum'] : $this->txt['lp_portal'];

		if ($this->txt['back'] === $this->txt['lp_portal']) {
			$this->txt['back'] = $this->txt['lp_forum'];
			$this->context['error_link'] .= '">' . $this->txt['lp_portal'] . '</a><a class="button floatnone" href="' . $this->scripturl . '?action=forum';
		}
	}

	private function setMeta()
	{
		if (empty($this->context['lp_page']))
			return;

		$this->context['meta_description'] = $this->context['lp_page']['description'];

		$keywords = [];
		if (! empty($this->context['lp_page']['tags'])) {
			$keywords = array_column($this->context['lp_page']['tags'], 'name');

			$this->modSettings['meta_keywords'] = implode(', ', $keywords);
		}

		$this->context['meta_tags'][] = ['prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'og:type', 'content' => 'article'];
		$this->context['meta_tags'][] = ['prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'article:author', 'content' => $this->context['lp_page']['author']];
		$this->context['meta_tags'][] = [
			'prefix'   => 'article: http://ogp.me/ns/article#',
			'property' => 'article:published_time',
			'content'  => date('Y-m-d\TH:i:s', (int) $this->context['lp_page']['created_at'])
		];

		if (! empty($this->context['lp_page']['updated_at']))
			$this->context['meta_tags'][] = [
				'prefix'   => 'article: http://ogp.me/ns/article#',
				'property' => 'article:modified_time',
				'content'  => date('Y-m-d\TH:i:s', (int) $this->context['lp_page']['updated_at'])
			];

		if (! empty($this->context['lp_page']['category']))
			$this->context['meta_tags'][] = ['prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'article:section', 'content' => $this->context['lp_page']['category']];

		if (! empty($keywords)) {
			foreach ($keywords as $value) {
				$this->context['meta_tags'][] = ['prefix' => 'article: http://ogp.me/ns/article#', 'property' => 'article:tag', 'content' => $value];
			}
		}

		if (! empty($this->modSettings['lp_page_og_image']) && ! empty($this->context['lp_page']['image']))
			$this->settings['og_image'] = $this->context['lp_page']['image'];
	}

	private function prepareRelatedPages()
	{
		if (empty($this->context['lp_page']['options']['show_related_pages']) || empty($this->modSettings['lp_show_related_pages']))
			return;

		$this->context['lp_page']['related_pages'] = $this->getRelatedPages();
	}

	private function prepareData(?array &$data)
	{
		if (empty($data))
			return;

		$is_author = ! empty($data['author_id']) && $data['author_id'] == $this->user_info['id'];

		$data['created']  = $this->getFriendlyTime((int) $data['created_at']);
		$data['updated']  = $this->getFriendlyTime((int) $data['updated_at']);
		$data['can_view'] = $this->canViewItem($data['permissions']) || $this->user_info['is_admin'] || $is_author;
		$data['can_edit'] = $this->user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $is_author);

		if ($data['type'] === 'bbc') {
			$this->require('Subs-Post');
			$data['content'] = un_preparsecode($data['content']);
		}

		if (! empty($data['category_id']))
			$data['category'] = $this->getAllCategories()[$data['category_id']]['name'];

		if (! empty($data['options']['keywords']))
			$data['tags'] = $this->getTags($data['options']['keywords']);

		$data['addons'] = '';

		$this->addon('preparePageData', [&$data, $is_author]);
	}

	private function prepareComments()
	{
		if (empty($this->modSettings['lp_show_comment_block']) || empty($this->context['lp_page']['options']['allow_comments']))
			return;

		if ($this->modSettings['lp_show_comment_block'] == 'none')
			return;

		loadLanguage('Editor');

		$this->addon('comments');

		if (! empty($this->context['lp_' . $this->modSettings['lp_show_comment_block'] . '_comment_block']))
			return;

		(new Comment($this->context['lp_page']['alias']))->prepare();
	}

	private function getTags(string $tags): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			WHERE FIND_IN_SET(tag_id, {string:tags})
			ORDER BY value',
			[
				'tags' => $tags
			]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['tag_id']] = [
				'name' => $row['value'],
				'href' => $this->scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $row['tag_id']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	private function updateNumViews()
	{
		if (empty($this->context['lp_page']['id']) || $this->user_info['possibly_robot'])
			return;

		if ($this->session()->isEmpty('light_portal_last_page_viewed') || $this->session()->get('light_portal_last_page_viewed') != $this->context['lp_page']['id']) {
			$this->smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}',
				[
					'item' => $this->context['lp_page']['id']
				]
			);

			$this->context['lp_num_queries']++;

			$this->session()->put('light_portal_last_page_viewed', $this->context['lp_page']['id']);
		}
	}
}
