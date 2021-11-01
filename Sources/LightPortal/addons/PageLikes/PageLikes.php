<?php

/**
 * PageLikes.php
 *
 * @package PageLikes (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\PageLikes;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;
use Likes;

/**
 * Generated by PluginMaker
 */
class PageLikes extends Plugin
{
	/** @var string */
	public $type = 'other';

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_valid_likes', __CLASS__ . '::validLikes#', false, __FILE__);
		add_integration_function('integrate_issue_like', __CLASS__ . '::issueLike#', false, __FILE__);
	}

	/**
	 * Validating data when like/unlike pages
	 *
	 * Валидируем данные при лайке/дизлайке страниц
	 *
	 * @param string $type
	 * @param int $content
	 * @return bool|array
	 */
	public function validLikes(string $type, int $content)
	{
		global $smcFunc, $user_info;

		if ($type !== 'lpp')
			return false;

		$request = $smcFunc['db_query']('', '
			SELECT alias, author_id
			FROM {db_prefix}lp_pages
			WHERE page_id = {int:id}
			LIMIT 1',
			array(
				'id' => $content
			)
		);

		[$alias, $author_id] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		if (empty($alias))
			return false;

		return [
			'type'        => $type,
			'flush_cache' => 'lp_likes_page_' . $content . '_' . $user_info['id'],
			'redirect'    => LP_PAGE_PARAM . '=' . $alias,
			'can_like'    => $user_info['id'] == $author_id ? 'cannot_like_content' : (allowedTo('likes_like') ? true : 'cannot_like_content')
		];
	}

	/**
	 * Update cache on like/unlike pages
	 *
	 * Обновляем кэш при лайке/дизлайке страниц
	 *
	 * @param Likes $obj
	 * @return void
	 */
	public function issueLike(Likes $obj)
	{
		if ($obj->get('type') !== 'lpp')
			return;

		Helpers::cache()->put('likes_page_' . $obj->get('content') . '_count', $obj->get('numLikes'));
	}

	/**
	 * @param array $data
	 * @param bool $is_author
	 * @return void
	 */
	public function preparePageData(array &$data, bool $is_author)
	{
		global $modSettings, $user_info;

		if (empty($modSettings['enable_likes']))
			return;

		loadJavaScriptFile('topic.js', array('defer' => false, 'minimize' => true), 'smf_topic');

		$user_likes = $user_info['is_guest'] ? [] : $this->prepareLikesContext($data['id']);

		$data['likes'] = array(
			'count'    => $this->getLikesCount($data['id']),
			'you'      => in_array($data['id'], $user_likes),
			'can_like' => ! $user_info['is_guest'] && ! $is_author && allowedTo('likes_like')
		);

		ob_start();

		$this->loadTemplate();

		show_likes_block($data);

		$data['addons'] .= ob_get_clean();
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
}
