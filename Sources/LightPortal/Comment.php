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
		global $smcFunc, $user_info;

		$parent  = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT);
		$i       = filter_input(INPUT_POST, 'i', FILTER_VALIDATE_INT);
		$page_id = filter_input(INPUT_POST, 'page_id', FILTER_VALIDATE_INT);
		$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
		$start   = filter_input(INPUT_POST, 'start', FILTER_VALIDATE_INT);

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
				'author_id'   => $user_info['id'],
				'author_name' => $user_info['name'],
				'message'     => $message,
				'created_at'  => date('Y-m-d', $time),
				'created'     => Helpers::getFriendlyTime($time)
			], $i + 1);

			$comment = ob_get_clean();

			$result = array(
				'item'    => $item,
				'parent'  => $parent,
				'comment' => $comment,
				'start'   => $start
			);

			clean_cache();
		}

		exit(json_encode($result));
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

		$item = filter_input(INPUT_POST, 'del_comment', FILTER_VALIDATE_INT);

		if (empty($item))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE id = {int:id}',
			array(
				'id' => $item
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE parent_id = {int:id}',
			array(
				'id' => $item
			)
		);

		clean_cache();
	}

	/**
	 * Get all comments (or for the current page only)
	 *
	 * Получаем все комментарии (или только для конкретной страницы)
	 *
	 * @param int $page_id
	 * @return array
	 */
	public static function getAll($page_id = null)
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT com.id, com.parent_id, com.page_id, com.author_id, com.message, com.created_at, mem.real_name AS author_name
			FROM {db_prefix}lp_comments AS com
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = com.author_id)' . (!empty($page_id) ? '
			WHERE com.page_id = {int:id}' : ''),
			array(
				'id' => $page_id
			)
		);

		$comments = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['message']);

			$comments[$row['id']] = array(
				'id'          => $row['id'],
				'page_id'     => $row['page_id'],
				'parent_id'   => $row['parent_id'],
				'author_id'   => $row['author_id'],
				'message'     => $row['message'],
				'created_at'  => date('Y-m-d', $row['created_at']),
				'created'     => Helpers::getFriendlyTime($row['created_at']),
				'author_name' => $row['author_name']
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
