<?php

namespace Bugo\LightPortal;

/**
 * Page.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
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
	 */
	public static function show()
	{
		global $modSettings, $context, $txt, $scripturl;

		isAllowedTo('light_portal_view');

		$alias = Helpers::request('page');

		if (empty($alias) && !empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] == 1 && !empty($modSettings['lp_frontpage_alias'])) {
			$context['lp_page'] = self::getDataByAlias($modSettings['lp_frontpage_alias']);
		} else {
			$alias = explode(';', $alias)[0];
			$context['lp_page'] = self::getDataByAlias($alias);
		}

		if (empty($context['lp_page'])) {
			self::changeBackButton();
			fatal_lang_error('lp_page_not_found', false, null, 404);
		}

		if (empty($context['lp_page']['can_view'])) {
			self::changeBackButton();
			fatal_lang_error('cannot_light_portal_view_page', false);
		}

		if (empty($context['lp_page']['status']) && empty($context['lp_page']['can_edit'])) {
			self::changeBackButton();
			fatal_lang_error('lp_page_not_activated', false);
		}

		if ($context['lp_page']['created_at'] > time())
			send_http_status(404);

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
			$context['linktree'][] = array(
				'name' => $context['page_title']
			);
		}

		loadTemplate('LightPortal/ViewPage');
		$context['sub_template'] = 'show_page';

		self::setMeta();
		self::prepareRelatedPages();
		self::prepareComments();
		self::updateNumViews();
	}

	/**
	 * Change back button text and back button href
	 *
	 * Меняем текст и href кнопки «Назад»
	 *
	 * @return void
	 */
	private static function changeBackButton()
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
	private static function setMeta()
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

		if (!empty($modSettings['lp_page_og_image']) && !empty($context['lp_page']['image']))
			$settings['og_image'] = $context['lp_page']['image'];
	}

	/**
	 * Prepare related pages list
	 *
	 * Формируем список похожих страниц
	 *
	 * @return void
	 */
	private static function prepareRelatedPages()
	{
		global $context, $modSettings;

		if (empty($context['lp_page']['options']['show_related_pages']) || empty($modSettings['lp_show_related_pages']))
			return;

		$context['lp_page']['related_pages'] = self::getRelatedPages();
	}

	/**
	 * Get an array of related pages
	 *
	 * Получаем массив похожих страниц
	 *
	 * @return array
	 */
	public static function getRelatedPages()
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
	 * Prepare comments to output
	 *
	 * Подготавливаем комментарии для отображения
	 *
	 * @return void
	 */
	private static function prepareComments()
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
	public static function getData(array $params)
	{
		global $smcFunc, $txt, $modSettings, $context;

		if (empty($params))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions, p.status, p.num_views, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, pt.lang, pt.title, pp.name, pp.value, t.value AS keyword
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {string:type})
				LEFT JOIN {db_prefix}lp_tags AS t ON (p.page_id = t.page_id)
			WHERE p.' . (!empty($params['alias']) ? 'alias = {string:alias}' : 'page_id = {int:item}'),
			array_merge(
				$params,
				array(
					'guest' => $txt['guest_title'],
					'type'  => 'page'
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
					'image'       => $og_image
				);

			if (!empty($row['lang']))
				$data['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$data['options'][$row['name']] = $row['value'];

			if (!empty($row['keyword']))
				$data['keywords'][] = $row['keyword'];
		}

		if (!empty($data['keywords']))
			$data['keywords'] = array_unique($data['keywords']);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * Get the page fields by its alias
	 *
	 * Получаем поля страницы по её алиасу
	 *
	 * @param string $alias
	 * @return array
	 */
	public static function getDataByAlias(string $alias)
	{
		if (empty($alias))
			return [];

		$data = Helpers::cache('page_' . $alias, 'getData', __CLASS__, LP_CACHE_TIME, array('alias' => $alias));
		self::prepareData($data);

		return $data;
	}

	/**
	 * Get the page fields
	 *
	 * Получаем поля страницы
	 *
	 * @param int $item
	 * @return array
	 */
	public static function getDataByItem(int $item)
	{
		if (empty($item))
			return [];

		$data = self::getData(array('item' => $item));
		self::prepareData($data);

		return $data;
	}

	/**
	 * Additional processing of page data
	 *
	 * Дополнительная обработка данных страницы
	 *
	 * @param array|null $data
	 * @return void
	 */
	private static function prepareData(?array &$data)
	{
		global $user_info;

		if (empty($data))
			return;

		$is_author = !empty($data['author_id']) && $data['author_id'] == $user_info['id'];

		$data['created']  = Helpers::getFriendlyTime($data['created_at']);
		$data['updated']  = Helpers::getFriendlyTime($data['updated_at']);
		$data['can_view'] = Helpers::canViewItem($data['permissions']) || $user_info['is_admin'] || $is_author;
		$data['can_edit'] = $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $is_author);
		$data['keywords'] = $data['keywords'] ?? [];
	}

	/**
	 * Increasing the number of page views
	 *
	 * Увеличиваем количество просмотров страницы
	 *
	 * @return void
	 */
	private static function updateNumViews()
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

			Helpers::session()->put('light_portal_last_page_viewed', $context['lp_page']['id']);
			$smcFunc['lp_num_queries']++;
		}
	}
}
