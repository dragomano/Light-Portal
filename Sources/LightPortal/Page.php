<?php

namespace Bugo\LightPortal;

/**
 * Page.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Page
{
	/**
	 * Display the page by its alias
	 *
	 * Просматриваем страницу по её алиасу
	 *
	 * @param string $alias
	 * @return void
	 */
	public static function show($alias = '/')
	{
		global $context, $modSettings, $txt, $scripturl;

		isAllowedTo('light_portal_view');

		self::relatedActions();

		$alias = explode(';', $alias)[0];

		$context['lp_page'] = self::getData($alias);

		if ($context['lp_page']['can_show'] === false && !$context['user']['is_admin'])
			fatal_lang_error('cannot_light_portal_view_page', false);

		Subs::parseContent($context['lp_page']['content'], $context['lp_page']['type']);
		Subs::setMeta();

		if (empty($context['current_action']))
			Block::display();

		if ($alias === '/') {
			$context['page_title']    = $modSettings['lp_frontpage_title_' . $context['user']['language']] ?? $txt['lp_portal'];
			$context['canonical_url'] = $scripturl;
		} else {
			$context['page_title']    = $context['lp_page']['title'];
			$context['canonical_url'] = $scripturl . '?page=' . $alias;
		}

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		self::prepareComments($alias);

		if (!empty($modSettings['lp_frontpage_mode']) && empty($_GET['page'])) {
			self::calculateNumColumns();

			loadTemplate('LightPortal/ViewFrontPage');

			if ($modSettings['lp_frontpage_mode'] == 1) {
				Subs::prepareArticles('topics');
				$context['sub_template'] = 'show_topics_as_articles';
			} elseif ($modSettings['lp_frontpage_mode'] == 2) {
				Subs::prepareArticles();
				$context['sub_template'] = 'show_pages_as_articles';
			} else {
				Subs::prepareArticles('boards');
				$context['sub_template'] = 'show_boards_as_articles';
			}
		} else {
			loadTemplate('LightPortal/ViewPage');
			$context['sub_template'] = 'show_page';
			self::updateNumViews();
		}

		if (isset($_REQUEST['page'])) {
			if ($_REQUEST['page'] !== $alias)
				redirectexit('page=' . $alias);
			elseif ($_REQUEST['page'] === '/')
				redirectexit();
		}
	}

	/**
	 * Various actions on portal pages
	 *
	 * Различные действия на страницах портала
	 *
	 * @return void
	 */
	private static function relatedActions()
	{
		if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'tags')
			Tag::show();

		if (isset($_REQUEST['new_comment']))
			Comment::add();

		if (isset($_REQUEST['del_comment']))
			Comment::remove();
	}

	/**
	 * Prepare comments to output
	 *
	 * Подготавливаем комментарии для отображения
	 *
	 * @param string $alias
	 * @return void
	 */
	private static function prepareComments($alias)
	{
		global $modSettings, $context, $txt;

		if (empty($modSettings['lp_show_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
			return;

		Subs::runAddons('comments');

		if (!empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block']))
			return;

		if (isset($_SESSION['lp_update_comments'])) {
			clean_cache();
			unset($_SESSION['lp_update_comments']);
		}

		$comments = Helpers::useCache(
			'page_' . ($alias == '/' ? 'main' : $alias) . '_comments',
			'getAll',
			'\Bugo\LightPortal\Comment',
			3600,
			$context['lp_page']['id']
		);

		$comments = array_map(
			function ($comment) {
				$date = date('Y-m-d', $comment['created_at']);
				$comment['created'] = Helpers::getFriendlyTime($comment['created_at']);
				$comment['created_at'] = $date;

				return $comment;
			},
			$comments
		);

		$total_comments     = count($comments);
		$txt['lp_comments'] = Helpers::getCorrectDeclension($total_comments, $txt['lp_comments_set']);

		$limit          = $modSettings['lp_num_comments_per_page'] ?? 10;
		$comment_tree   = Comment::getCommentTree($comments);
		$total_comments = count($comment_tree);

		$context['page_index'] = constructPageIndex($context['canonical_url'], $_REQUEST['start'], $total_comments, $limit);
		$context['start']      = &$_REQUEST['start'];
		$start                 = (int) $_REQUEST['start'];

		$context['lp_page']['comments'] = array_slice($comment_tree, $start, $limit);

		$context['page_info'] = array(
			'current_page' => $_REQUEST['start'] / $limit + 1,
			'num_pages'    => floor(($total_comments - 1) / $limit) + 1,
			'next_page'    => $_REQUEST['start'] + $limit < $total_comments ? $context['canonical_url'] . ';start=' . ($_REQUEST['start'] + $limit) : ''
		);
	}

	/**
	 * Calculate the number columns for the frontpage layout
	 *
	 * Подсчитываем количество колонок для макета главной страницы
	 *
	 * @return void
	 */
	private static function calculateNumColumns()
	{
		global $modSettings, $context;

		$num_columns = 12;

		if (!empty($modSettings['lp_frontpage_layout'])) {
			switch ($modSettings['lp_frontpage_layout']) {
				case '1':
					$num_columns = 12 / 2;
					break;
				case '2':
					$num_columns = 12 / 3;
					break;
				case '3':
					$num_columns = 12 / 4;
					break;
				default:
					$num_columns = 12 / 6;
			}
		}

		$context['lp_frontpage_layout'] = $num_columns;
	}

	/**
	 * Get the page data from lp_pages table
	 *
	 * Получаем данные страницы из таблицы в базе данных
	 *
	 * @param array $params
	 * @return void
	 */
	public static function getFromDB($params)
	{
		global $smcFunc, $modSettings;

		[$item, $isAlias] = $params;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.description, p.keywords, p.content, p.type, p.permissions, p.status, p.num_views, p.created_at, p.updated_at,
				mem.real_name AS author_name, pp.name, pp.value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
				LEFT JOIN {db_prefix}lp_params AS pp ON (pp.item_id = p.page_id AND pp.type = {string:type})
			WHERE ' . ($isAlias ? 'p.alias = {string' : 'p.page_id = {int') . ':item}',
			array(
				'type' => 'page',
				'item' => $item
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error('lp_page_not_found', false, null, 404);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			$og_image = null;
			if (!empty($modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				Subs::parseContent($content, $row['type']);
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
					'title'       => $row['title'],
					'alias'       => $row['alias'],
					'description' => $row['description'],
					'keywords'    => $row['keywords'],
					'content'     => $row['content'],
					'type'        => $row['type'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'num_views'   => $row['num_views'],
					'created_at'  => $row['created_at'],
					'updated_at'  => $row['updated_at'],
					'image'       => $og_image
				);

			if (!empty($row['name']))
				$data['options'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);

		return $data;
	}

	/**
	 * Get the page fields
	 *
	 * Получаем поля страницы
	 *
	 * @param mixed $item
	 * @param bool $isAlias
	 * @return array
	 */
	public static function getData($item, $isAlias = true)
	{
		global $user_info;

		if (empty($item))
			return;

		$data = Helpers::useCache('page_' . ($item == '/' ? 'main' : $item), 'getFromDB', __CLASS__, 3600, array($item, $isAlias));

		if (!empty($data)) {
			$data['created']  = Helpers::getFriendlyTime($data['created_at']);
			$data['updated']  = Helpers::getFriendlyTime($data['updated_at']);
			$data['can_show'] = Helpers::canShowItem($data['permissions']);
			$data['can_edit'] = $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $data['author_id'] == $user_info['id']);
		}

		return $data;
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
		global $context, $smcFunc;

		if (empty($context['lp_page']['id']))
			return;

		if (empty($_SESSION['light_portal_last_page_viewed']) || $_SESSION['light_portal_last_page_viewed'] != $context['lp_page']['id']) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}',
				array(
					'item' => $context['lp_page']['id']
				)
			);

			$_SESSION['light_portal_last_page_viewed'] = $context['lp_page']['id'];
		}
	}
}
