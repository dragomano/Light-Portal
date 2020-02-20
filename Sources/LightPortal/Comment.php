<?php

namespace Bugo\LightPortal;

/**
 * Comment.php
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

class Comment
{
	/**
	 * Adding a comment
	 *
	 * Добавление комментария
	 *
	 * @return void
	 */
	public static function add()
	{
		global $smcFunc, $user_info, $txt;

		$args = array(
			'parent_id'  => FILTER_VALIDATE_INT,
			'counter'    => FILTER_VALIDATE_INT,
			'level'      => FILTER_VALIDATE_INT,
			'page_id'    => FILTER_VALIDATE_INT,
			'page_title' => FILTER_SANITIZE_STRING,
			'page_alias' => FILTER_SANITIZE_STRING,
			'page_url'   => FILTER_SANITIZE_STRING,
			'message'    => FILTER_SANITIZE_STRING,
			'start'      => FILTER_VALIDATE_INT
		);

		$data = filter_input_array(INPUT_POST, $args);

		$parent     = $data['parent_id'];
		$counter    = $data['counter'];
		$level      = $data['level'];
		$page_id    = $data['page_id'];
		$page_title = $data['page_title'];
		$page_alias = $data['page_alias'];
		$page_url   = $data['page_url'];
		$message    = $data['message'];
		$start      = $data['start'];

		if (empty($page_id) || empty($message))
			return;

		$item = $smcFunc['db_insert']('',
			'{db_prefix}lp_comments',
			array(
				'parent_id'  => 'int',
				'page_id'    => 'int',
				'author_id'  => 'int',
				'message'    => 'string-' . Helpers::getMaxMessageLength(),
				'created_at' => 'int'
			),
			array(
				$parent,
				$page_id,
				$user_info['id'],
				$message,
				$time = time()
			),
			array('id'),
			1
		);

		$result['error'] = true;

		if (!empty($item)) {
			loadTemplate('LightPortal/ViewPage');

			ob_start();

			show_single_comment([
				'id'          => $item,
				'alias'       => $page_alias,
				'author_id'   => $user_info['id'],
				'author_name' => $user_info['name'],
				'avatar'      => self::getUserAvatar(),
				'message'     => $message,
				'created_at'  => date('Y-m-d', $time),
				'created'     => Helpers::getFriendlyTime($time)
			], $counter + 1, $level + 1);

			$comment = ob_get_clean();

			$result = array(
				'item'     => $item,
				'parent'   => $parent,
				'comment'  => $comment,
				'created'  => $time,
				'title'    => $txt['response_prefix'] . $page_title,
				'alias'    => $page_alias,
				'page_url' => $page_url,
				'start'    => $start
			);

			if (empty($parent))
				self::makeNotify('new_comment', 'page_comment', $result);
			else
				self::makeNotify('new_reply', 'page_comment_reply', $result);

			Helpers::useCache('page_' . ($page_alias == '/' ? 'main' : $page_alias) . '_comments', null);
		}

		exit(json_encode($result));
	}

	/**
	 * Get user avatar image (html string)
	 *
	 * Получение аватарки пользователя (готовый HTML-код)
	 *
	 * @return string
	 */
	public static function getUserAvatar()
	{
		global $modSettings, $user_info, $scripturl, $smcFunc;

		if (!empty($user_avatar))
			return;

		$user_avatar = array();

		if (($modSettings['gravatarEnabled'] && substr($user_info['avatar']['url'], 0, 11) == 'gravatar://') || !empty($modSettings['gravatarOverride'])) {
			if (!empty($modSettings['gravatarAllowExtraEmail']) && stristr($user_info['avatar']['url'], 'gravatar://') && strlen($user_info['avatar']['url']) > 11)
				$user_avatar['href'] = get_gravatar_url($smcFunc['substr']($user_info['avatar']['url'], 11));
			else
				$user_avatar['href'] = get_gravatar_url($user_info['email']);
		} elseif ($user_info['avatar']['url'] == '' && !empty($user_info['avatar']['id_attach'])) {
			$user_avatar['href'] = $user_info['avatar']['custom_dir'] ? $modSettings['custom_avatar_url'] . '/' . $user_info['avatar']['filename'] : $scripturl . '?action=dlattach;attach=' . $user_info['avatar']['id_attach'] . ';type=avatar';
		} elseif (strpos($user_info['avatar']['url'], 'http://') === 0 || strpos($user_info['avatar']['url'], 'https://') === 0) {
			$user_avatar['href'] = $user_info['avatar']['url'];
		} elseif ($user_info['avatar']['url'] != '') {
			$user_avatar['href'] = $modSettings['avatar_url'] . '/' . $smcFunc['htmlspecialchars']($user_info['avatar']['url']);
		} else
			$user_avatar['href'] = $modSettings['avatar_url'] . '/default.png';

		if (!empty($user_avatar))
			$user_avatar['image'] = '<img src="' . $user_avatar['href'] . '" alt="" class="avatar">';

		return $user_avatar['image'];
	}

	/**
	 * Creating a background task to notify subscribers of new comments
	 *
	 * Создаем фоновую задачу для уведомления подписчиков о новых комментариях
	 *
	 * @param string $type
	 * @param string $action
	 * @param array $commentOptions
	 * @return void
	 */
	private static function makeNotify(string $type, string $action, array $commentOptions = [])
	{
		global $smcFunc, $user_info;

		if (empty($commentOptions))
			return;

		$smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			array(
				'task_file'    => 'string',
				'task_class'   => 'string',
				'task_data'    => 'string',
				'claimed_time' => 'int'
			),
			array(
				'$sourcedir/LightPortal/Notify.php',
				'\Bugo\LightPortal\Notify',
				$smcFunc['json_encode'](
					array(
						'time'           => $commentOptions['created'],
						'member_id'	     => $user_info['id'],
						'member_name'    => $user_info['name'],
						'content_type'   => $type,
						'content_id'     => $commentOptions['item'],
						'content_action' => $action,
						'extra'          => json_encode(
							array(
								'content_subject' => $commentOptions['title'],
								'content_link'    => $commentOptions['page_url'] . "start=" . $commentOptions['start'] . '#comment' . $commentOptions['item']
							)
						)
					)
				),
				0
			),
			array('id_task')
		);
	}

	/**
	 * Deleting a comment
	 *
	 * Удаление комментария (и всех дочерних)
	 *
	 * @return void
	 */
	public static function remove()
	{
		global $smcFunc;

		$items = filter_input(INPUT_POST, 'items', FILTER_VALIDATE_INT, array('flags'  => FILTER_REQUIRE_ARRAY));
		$alias = filter_input(INPUT_POST, 'alias', FILTER_SANITIZE_STRING);

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		Helpers::useCache('page_' . ($alias == '/' ? 'main' : $alias) . '_comments', null);
	}

	/**
	 * Get all comments (or for the current page only)
	 *
	 * Получаем все комментарии (или только для конкретной страницы)
	 *
	 * @param int $page_id
	 * @return array
	 */
	public static function getAll(int $page_id = 0)
	{
		global $smcFunc, $memberContext;

		$request = $smcFunc['db_query']('', '
			SELECT com.id, com.parent_id, com.page_id, com.author_id, com.message, com.created_at, mem.real_name AS author_name
			FROM {db_prefix}lp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = com.author_id)' . (!empty($page_id) ? '
			WHERE com.page_id = {int:id}' : ''),
			array(
				'id' => $page_id
			)
		);

		$comments = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['message']);

			if (!isset($memberContext[$row['author_id']])) {
				loadMemberData($row['author_id']);
				loadMemberContext($row['author_id']);
			}

			$comments[$row['id']] = array(
				'id'          => $row['id'],
				'page_id'     => $row['page_id'],
				'parent_id'   => $row['parent_id'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'avatar'      => $memberContext[$row['author_id']]['avatar']['image'],
				'message'     => $row['message'],
				'created_at'  => $row['created_at']
			);
		}

		$smcFunc['db_free_result']($request);

		return $comments;
	}

	/**
	 * Get comment tree (parents and childs)
	 *
	 * Получаем дерево комментариев
	 *
	 * @param array $data
	 * @return array
	 */
	public static function getCommentTree(array $data)
	{
		$tree = [];

		foreach ($data as $id => &$node) {
			if (empty($node['parent_id']))
				$tree[$id] = &$node;
			else
				$data[$node['parent_id']]['childs'][$id] = &$node;
		}

		return $tree;
	}
}
