<?php declare(strict_types=1);

/**
 * Page.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Config, Content, ErrorHandler};
use Bugo\LightPortal\Utils\{Icon, Lang, Theme, User, Utils};
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class Page implements PageInterface
{
	use Helper;

	/**
	 * @throws IntlException
	 */
	public function show(): void
	{
		$this->middleware('light_portal_view');

		$alias = $this->request(LP_PAGE_PARAM);

		if (empty($alias)) {
			if (Config::$modSettings['lp_frontpage_mode'] && Config::$modSettings['lp_frontpage_mode'] === 'chosen_page' && Config::$modSettings['lp_frontpage_alias']) {
				Utils::$context['lp_page'] = $this->getDataByAlias(Config::$modSettings['lp_frontpage_alias']);
			} else {
				Config::updateModSettings(['lp_frontpage_mode' => 0]);
			}
		} else {
			$alias = explode(';', $alias)[0];

			if ($this->isFrontpage($alias))
				Utils::redirectexit('action=' . LP_ACTION);

			Utils::$context['lp_page'] = $this->getDataByAlias($alias);
		}

		if (empty(Utils::$context['lp_page'])) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('lp_page_not_found', status: 404);
		}

		if (empty(Utils::$context['lp_page']['can_view'])) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('cannot_light_portal_view_page');
		}

		if (empty(Utils::$context['lp_page']['status']) && empty(Utils::$context['lp_page']['can_edit'])) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('lp_page_not_activated');
		}

		if (Utils::$context['lp_page']['created_at'] > time())
			Utils::sendHttpStatus(404);

		Utils::$context['lp_page']['errors'] = [];
		if (empty(Utils::$context['lp_page']['status']) && Utils::$context['lp_page']['can_edit'])
			Utils::$context['lp_page']['errors'][] = Lang::$txt['lp_page_visible_but_disabled'];

		Utils::$context['lp_page']['content'] = Content::parse(Utils::$context['lp_page']['content'], Utils::$context['lp_page']['type']);

		if (empty($alias)) {
			Utils::$context['page_title']    = $this->getTranslatedTitle(Utils::$context['lp_page']['titles']) ?: Lang::$txt['lp_portal'];
			Utils::$context['canonical_url'] = Config::$scripturl;
			Utils::$context['linktree'][]    = ['name' => Lang::$txt['lp_portal']];
		} else {
			Utils::$context['page_title']    = $this->getTranslatedTitle(Utils::$context['lp_page']['titles']) ?: Lang::$txt['lp_post_error_no_title'];
			Utils::$context['canonical_url'] = LP_PAGE_URL . $alias;

			if (isset(Utils::$context['lp_page']['category'])) {
				Utils::$context['linktree'][] = [
					'name' => Utils::$context['lp_page']['category'],
					'url'  => LP_BASE_URL . ';sa=categories;id=' . Utils::$context['lp_page']['category_id']
				];
			}

			Utils::$context['linktree'][] = [
				'name' => Utils::$context['page_title']
			];
		}

		Utils::$context['lp_page']['url'] = Utils::$context['canonical_url'] . ($this->request()->has(LP_PAGE_PARAM) ? ';' : '?');

		Theme::loadTemplate('LightPortal/ViewPage');

		Utils::$context['sub_template'] = 'show_page';

		$this->promote();
		$this->setMeta();
		$this->preparePrevNextLinks();
		$this->prepareRelatedPages();
		$this->prepareComments();
		$this->updateNumViews();

		Theme::loadJSFile('light_portal/bundle.min.js', ['defer' => true]);
	}

	public function getData(array $params): array
	{
		if (empty($params))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
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
					'guest' => Lang::$txt['guest_title']
				]
			)
		);

		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			Lang::censorText($row['content']);

			$og_image = null;
			if (! empty(Config::$modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				$content = Content::parse($content, $row['type']);
				$image_found = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

				if ($image_found && is_array($values)) {
					$all_images = array_pop($values);
					$image      = Config::$modSettings['lp_page_og_image'] == 1 ? array_shift($all_images) : array_pop($all_images);
					$og_image   = Utils::$smcFunc['htmlspecialchars']($image);
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

			$data['titles'][$row['lang']] = $row['title'];

			$data['options'][$row['name']] = $row['value'];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * @throws IntlException
	 */
	public function getDataByAlias(string $alias): array
	{
		if (empty($alias))
			return [];

		$data = $this->cache('page_' . $alias)->setFallback(self::class, 'getData', ['alias' => $alias]);

		$this->prepareData($data);

		return $data;
	}

	/**
	 * @throws IntlException
	 */
	public function getDataByItem(int $item): array
	{
		if (empty($item))
			return [];

		$data = $this->getData(['item' => $item]);

		$this->prepareData($data);

		return $data;
	}

	public function showAsCards(AbstractPageList $entity): void
	{
		$start = (int) $this->request('start');
		$limit = (int) Config::$modSettings['lp_num_items_per_page'] ?? 12;

		$itemsCount = $entity->getTotalCount();

		$front = new FrontPage;
		$front->updateStart($itemsCount, $start, $limit);

		$sort     = $front->getOrderBy();
		$articles = $entity->getPages($start, $limit, $sort);

		Utils::$context['page_index'] = $this->constructPageIndex(Utils::$context['canonical_url'], $this->request()->get('start'), $itemsCount, $limit);
		Utils::$context['start']      = $this->request()->get('start');

		Utils::$context['lp_frontpage_articles']    = $articles;
		Utils::$context['lp_frontpage_num_columns'] = $front->getNumColumns();

		Utils::$context['template_layers'][] = 'sorting';

		$front->prepareTemplates();

		Utils::obExit();
	}

	public function getList(): array
	{
		return [
			'items_per_page' => Config::$modSettings['defaultMaxListItems'] ?: 50,
			'title' => Utils::$context['page_title'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'date',
			'columns' => [
				'date' => [
					'header' => [
						'value' => Lang::$txt['date']
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
						'value' => Lang::$txt['lp_title']
					],
					'data' => [
						'function' => fn($entry) => '<a class="bbc_link' . (
							$entry['is_front']
								? ' new_posts" href="' . Config::$scripturl
								: '" href="' . LP_PAGE_URL . $entry['alias']
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
						'value' => Lang::$txt['author']
					],
					'data' => [
						'function' => fn($entry) => empty($entry['author']['name']) ? Lang::$txt['guest_title'] : '<a href="' . $entry['author']['link'] . '">' . $entry['author']['name'] . '</a>',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'author_name DESC',
						'reverse' => 'author_name'
					]
				],
				'num_views' => [
					'header' => [
						'value' => Lang::$txt['views']
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
				'href' => Utils::$context['canonical_url']
			]
		];
	}

	private function changeErrorPage(): void
	{
		Utils::$context['error_link'] = Config::$scripturl;
		Lang::$txt['back'] = empty(Config::$modSettings['lp_frontpage_mode']) ? Lang::$txt['lp_forum'] : Lang::$txt['lp_portal'];

		if (Lang::$txt['back'] === Lang::$txt['lp_portal']) {
			Lang::$txt['back'] = Lang::$txt['lp_forum'];
			Utils::$context['error_link'] .= '">' . Lang::$txt['lp_portal'] . '</a> <a class="button floatnone" href="' . Config::$scripturl . '?action=forum';
		}
	}

	private function promote(): void
	{
		if (empty(User::$info['is_admin']) || $this->request()->hasNot('promote'))
			return;

		$page = Utils::$context['lp_page']['id'];

		if (($key = array_search($page, Utils::$context['lp_frontpage_pages'])) !== false) {
			unset(Utils::$context['lp_frontpage_pages'][$key]);
		} else {
			Utils::$context['lp_frontpage_pages'][] = $page;
		}

		Config::updateModSettings(['lp_frontpage_pages' => implode(',', Utils::$context['lp_frontpage_pages'])]);

		Utils::redirectexit(Utils::$context['canonical_url']);
	}

	private function setMeta(): void
	{
		if (empty(Utils::$context['lp_page']))
			return;

		Utils::$context['meta_description'] = Utils::$context['lp_page']['description'];

		$keywords = [];
		if (isset(Utils::$context['lp_page']['tags'])) {
			$keywords = array_column(Utils::$context['lp_page']['tags'], 'name');

			Config::$modSettings['meta_keywords'] = implode(', ', $keywords);
		}

		Utils::$context['meta_tags'][] = ['prefix' => 'article: https://ogp.me/ns/article#', 'property' => 'og:type', 'content' => 'article'];
		Utils::$context['meta_tags'][] = ['prefix' => 'article: https://ogp.me/ns/article#', 'property' => 'article:author', 'content' => Utils::$context['lp_page']['author']];
		Utils::$context['meta_tags'][] = [
			'prefix'   => 'article: https://ogp.me/ns/article#',
			'property' => 'article:published_time',
			'content'  => date('Y-m-d\TH:i:s', (int) Utils::$context['lp_page']['created_at'])
		];

		if (Utils::$context['lp_page']['updated_at'])
			Utils::$context['meta_tags'][] = [
				'prefix'   => 'article: https://ogp.me/ns/article#',
				'property' => 'article:modified_time',
				'content'  => date('Y-m-d\TH:i:s', (int) Utils::$context['lp_page']['updated_at'])
			];

		if (isset(Utils::$context['lp_page']['category']))
			Utils::$context['meta_tags'][] = ['prefix' => 'article: https://ogp.me/ns/article#', 'property' => 'article:section', 'content' => Utils::$context['lp_page']['category']];

		foreach ($keywords as $value) {
			Utils::$context['meta_tags'][] = ['prefix' => 'article: https://ogp.me/ns/article#', 'property' => 'article:tag', 'content' => $value];
		}

		if (! (empty(Config::$modSettings['lp_page_og_image']) || empty(Utils::$context['lp_page']['image'])))
			Theme::$current->settings['og_image'] = Utils::$context['lp_page']['image'];
	}


	private function preparePrevNextLinks(): void
	{
		if (empty(Utils::$context['lp_page']) || empty(Config::$modSettings['lp_show_prev_next_links']))
			return;

		$titles = $this->getEntityList('title');

		$orders = [
			'CASE WHEN com.created_at > 0 THEN 0 ELSE 1 END, comment_date DESC',
			'p.created_at DESC',
			'p.created_at',
			'date DESC'
		];

		$within_category = str_contains(filter_input(INPUT_SERVER, 'HTTP_REFERER') ?? '', 'action=portal;sa=categories;id');

		$result = Utils::$smcFunc['db_query']('', '
			(
				SELECT p.page_id, p.alias, GREATEST(p.created_at, p.updated_at) AS date, CASE WHEN COALESCE(par.value, \'0\') != \'0\' THEN p.num_comments ELSE 0 END AS num_comments, com.created_at AS comment_date
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_comments AS com ON (p.last_comment_id = com.id)
					LEFT JOIN {db_prefix}lp_params AS par ON (par.item_id = com.page_id AND par.type = {literal:page} AND par.name = {literal:allow_comments})
				WHERE p.page_id != {int:page_id}' . ($within_category ? '
					AND p.category_id = {int:category_id}' : '') . '
					AND p.created_at <= {int:created_at}
					AND p.created_at <= {int:current_time}
					AND p.status = {int:status}
					AND p.permissions IN ({array_int:permissions})
					ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 'num_comments DESC, ') . $orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT 1
			)
			UNION ALL
			(
				SELECT p.page_id, p.alias, GREATEST(p.created_at, p.updated_at) AS date, CASE WHEN COALESCE(par.value, \'0\') != \'0\' THEN p.num_comments ELSE 0 END AS num_comments, com.created_at AS comment_date
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_comments AS com ON (p.last_comment_id = com.id)
					LEFT JOIN {db_prefix}lp_params AS par ON (par.item_id = com.page_id AND par.type = {literal:page} AND par.name = {literal:allow_comments})
				WHERE p.page_id != {int:page_id}' . ($within_category ? '
					AND p.category_id = {int:category_id}' : '') . '
					AND p.created_at >= {int:created_at}
					AND p.created_at <= {int:current_time}
					AND p.status = {int:status}
					AND p.permissions IN ({array_int:permissions})
				ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 'num_comments DESC, ') . $orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT 1
			)',
			[
				'page_id'      => Utils::$context['lp_page']['id'],
				'category_id'  => Utils::$context['lp_page']['category_id'],
				'created_at'   => Utils::$context['lp_page']['created_at'],
				'current_time' => time(),
				'status'       => Utils::$context['lp_page']['status'],
				'permissions'  => $this->getPermissions()
			]
		);

		[$prev_id, $prev_alias] = Utils::$smcFunc['db_fetch_row']($result);
		[$next_id, $next_alias] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		if (! empty($prev_alias)) {
			Utils::$context['lp_page']['prev'] = [
				'link'  => LP_PAGE_URL . $prev_alias,
				'title' => $this->getTranslatedTitle($titles[$prev_id])
			];
		}

		if (! empty($next_alias)) {
			Utils::$context['lp_page']['next'] = [
				'link'  => LP_PAGE_URL . $next_alias,
				'title' => $this->getTranslatedTitle($titles[$next_id])
			];
		}
	}

	private function prepareRelatedPages(): void
	{
		if (empty($item = Utils::$context['lp_page']) || empty(Config::$modSettings['lp_show_related_pages']) || empty(Utils::$context['lp_page']['options']['show_related_pages']))
			return;

		$title_words = explode(' ', $this->getTranslatedTitle($item['titles']));
		$alias_words = explode('_', $item['alias']);

		$search_formula = '';
		foreach ($title_words as $key => $word) {
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 2 . ' ELSE 0 END';
		}

		foreach ($alias_words as $key => $word) {
			$search_formula .= ' + CASE WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\') THEN ' . (count($alias_words) - $key) . ' ELSE 0 END';
		}

		$result = Utils::$smcFunc['db_query']('', '
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
				'current_lang' => Utils::$context['user']['language'],
				'status'       => Utils::$context['lp_page']['status'],
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'current_page' => $item['id']
			]
		);

		Utils::$context['lp_page']['related_pages'] = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$image = $this->getImageFromText($row['content']);

			Utils::$context['lp_page']['related_pages'][$row['page_id']] = [
				'id'    => $row['page_id'],
				'title' => $row['title'],
				'alias' => $row['alias'],
				'link'  => LP_PAGE_URL . $row['alias'],
				'image' => $image ?: (Config::$modSettings['lp_image_placeholder'] ?? '')
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;
	}

	/**
	 * @throws IntlException
	 */
	private function prepareData(?array &$data): void
	{
		if (empty($data))
			return;

		$is_author = $data['author_id'] && $data['author_id'] == User::$info['id'];

		$data['created']  = $this->getFriendlyTime((int) $data['created_at']);
		$data['updated']  = $this->getFriendlyTime((int) $data['updated_at']);
		$data['can_view'] = $this->canViewItem($data['permissions']) || User::$info['is_admin'] || $is_author;
		$data['can_edit'] = User::$info['is_admin'] || Utils::$context['allow_light_portal_manage_pages_any'] || (Utils::$context['allow_light_portal_manage_pages_own'] && $is_author);

		if ($data['type'] === 'bbc') {
			$data['content'] = $this->unPreparseCode($data['content']);
		}

		if (! empty($data['category_id']))
			$data['category'] = $this->getEntityList('category')[$data['category_id']]['name'];

		if (! empty($data['options']['keywords']))
			$data['tags'] = $this->getTags($data['options']['keywords']);

		$this->hook('preparePageData', [&$data, $is_author]);
	}

	/**
	 * @throws IntlException
	 */
	private function prepareComments(): void
	{
		if (empty(Config::$modSettings['lp_show_comment_block']) || empty(Utils::$context['lp_page']['options']['allow_comments']))
			return;

		if (Config::$modSettings['lp_show_comment_block'] === 'none')
			return;

		Lang::load('Editor');

		$this->hook('comments');

		if (isset(Utils::$context['lp_' . Config::$modSettings['lp_show_comment_block'] . '_comment_block']))
			return;

		$this->prepareJsonData();

		(new Comment(Utils::$context['lp_page']['alias']))->prepare();
	}

	private function prepareJsonData(): void
	{
		$txtData = [
			'pages'         => Lang::$txt['pages'],
			'author'        => Lang::$txt['author'],
			'reply'         => Lang::$txt['reply'],
			'modify'        => Lang::$txt['modify'],
			'modify_cancel' => Lang::$txt['modify_cancel'],
			'remove'        => Lang::$txt['remove'],
			'add_comment'   => Lang::$txt['lp_comment_placeholder'],
			'post'          => Lang::$txt['post'],
			'save'          => Lang::$txt['save'],
			'title'         => Lang::$txt['lp_comments_title'],
			'prev'          => Lang::$txt['prev'],
			'next'          => Lang::$txt['next'],
		];

		$pageUrl = Utils::$context['lp_page']['url'];

		// @TODO Need to improve this case
		if (class_exists('\SimpleSEF')) {
			$pageUrl = (new \SimpleSEF)->getSefUrl($pageUrl);
		}

		$contextData = [
			'locale'  => Lang::$txt['lang_dictionary'],
			'pageUrl' => $pageUrl,
			'charset' => Utils::$context['character_set'],
		];

		$settingsData = [
			'lp_comment_sorting' => Config::$modSettings['lp_comment_sorting'] ?? '0',
		];

		Utils::$context['lp_json']['txt']      = json_encode($txtData);
		Utils::$context['lp_json']['context']  = json_encode($contextData);
		Utils::$context['lp_json']['settings'] = json_encode($settingsData);
		Utils::$context['lp_json']['icons']    = json_encode(Icon::all());
		Utils::$context['lp_json']['user']     = json_encode(Utils::$context['user']);
	}

	private function getTags(string $tags): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT tag_id, value
			FROM {db_prefix}lp_tags
			WHERE FIND_IN_SET(tag_id, {string:tags}) > 0
			ORDER BY value',
			[
				'tags' => $tags
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['tag_id']] = [
				'name' => $row['value'],
				'href' => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id']
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	private function updateNumViews(): void
	{
		if (empty(Utils::$context['lp_page']['id']) || User::$info['possibly_robot'])
			return;

		if ($this->session()->isEmpty('light_portal_last_page_viewed') || $this->session()->get('light_portal_last_page_viewed') != Utils::$context['lp_page']['id']) {
			Utils::$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}
					AND status IN ({array_int:statuses})',
				[
					'item'     => Utils::$context['lp_page']['id'],
					'statuses' => [self::STATUS_ACTIVE, self::STATUS_INTERNAL]
				]
			);

			Utils::$context['lp_num_queries']++;

			$this->session()->put('light_portal_last_page_viewed', Utils::$context['lp_page']['id']);
		}
	}
}
