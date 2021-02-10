<?php

namespace Bugo\LightPortal;

/**
 * Comment.php
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

class Comment
{
	/**
	 * Page alias
	 *
	 * @var string
	 */
	private $alias;

	/**
	 * Comment construct
	 *
	 * @param string $alias
	 */
	public function __construct(string $alias = '')
	{
		$this->alias = $alias;
	}

	/**
	 * Process comments
	 *
	 * Обрабатываем комментарии
	 *
	 * @return void
	 */
	public function prepare()
	{
		global $context, $modSettings, $txt, $scripturl;

		if (empty($this->alias))
			return;

		$context['lp_allowed_bbc'] = !empty($modSettings['lp_enabled_bbc_in_comments']) ? explode(',', $modSettings['lp_enabled_bbc_in_comments']) : [];
		$context['lp_allowed_bbc'] = array_diff($context['lp_allowed_bbc'], array_intersect(explode(',', $modSettings['disabledBBC']), $context['lp_allowed_bbc']));

		if (Helpers::request()->filled('sa')) {
			switch (Helpers::request('sa')) {
				case 'new_comment':
					$this->add();
					break;

				case 'edit_comment':
					$this->edit();
					break;

				case 'del_comment':
					$this->remove();
					break;
			}
		}

		loadLanguage('Editor');

		$comments = Helpers::cache('page_' . $this->alias . '_comments', 'getAll', __CLASS__, LP_CACHE_TIME, $context['lp_page']['id']);
		$comments = array_map(
			function ($comment) {
				$comment['created']    = Helpers::getFriendlyTime($comment['created_at']);
				$comment['created_at'] = date('Y-m-d', $comment['created_at']);

				return $comment;
			},
			$comments
		);

		$total_comments     = sizeof($comments);
		$txt['lp_comments'] = Helpers::getText($total_comments, $txt['lp_comments_set']);

		$limit          = $modSettings['lp_num_comments_per_page'] ?? 10;
		$comment_tree   = $this->getTree($comments);
		$total_comments = sizeof($comment_tree);

		$page_index_url = $context['canonical_url'];
		if (!empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] == 'chosen_page' && !empty($modSettings['lp_frontpage_alias']))
			$page_index_url = $scripturl . '?action=portal';

		$temp_start            = Helpers::request('start');
		$context['page_index'] = constructPageIndex($page_index_url, Helpers::request()->get('start'), $total_comments, $limit);
		$start                 = Helpers::request('start');

		$context['page_info']['num_pages'] = floor($total_comments / $limit) + 1;
		$context['page_info']['start']     = $context['page_info']['num_pages'] * $limit - $limit;

		if ($temp_start > $total_comments)
			send_http_status(404);

		$context['lp_page']['comments'] = array_slice($comment_tree, $start, $limit);

		if ($context['user']['is_logged']) {
			addInlineJavaScript('
		const comment = new Comment("' . $context['lp_current_page_url'] . '", ' . $context['page_info']['start'] . ');
		const toolbar = new Toolbar();');
		}
	}

	/**
	 * Adding a comment
	 *
	 * Добавление комментария
	 *
	 * @return void
	 */
	private function add()
	{
		global $user_info, $smcFunc, $context, $txt;

		$result['error'] = true;

		if (empty($user_info['id']))
			exit(json_encode($result));

		$args = array(
			'parent_id'   => FILTER_VALIDATE_INT,
			'counter'     => FILTER_VALIDATE_INT,
			'level'       => FILTER_VALIDATE_INT,
			'page_id'     => FILTER_VALIDATE_INT,
			'page_title'  => FILTER_SANITIZE_STRING,
			'page_url'    => FILTER_SANITIZE_STRING,
			'message'     => FILTER_DEFAULT,
			'start'       => FILTER_VALIDATE_INT,
			'commentator' => FILTER_VALIDATE_INT
		);

		$data = filter_input_array(INPUT_POST, $args);

		if (empty($data))
			exit(json_encode($result));

		$parent      = $data['parent_id'];
		$counter     = $data['counter'];
		$level       = $data['level'];
		$page_id     = $data['page_id'];
		$page_title  = $data['page_title'];
		$page_url    = $data['page_url'];
		$message     = $smcFunc['htmlspecialchars']($data['message']);
		$start       = $data['start'];
		$commentator = $data['commentator'];

		if (empty($page_id) || empty($message))
			exit(json_encode($result));

		Helpers::require('Subs-Post');
		preparsecode($message);

		$item = $smcFunc['db_insert']('',
			'{db_prefix}lp_comments',
			array(
				'parent_id'  => 'int',
				'page_id'    => 'int',
				'author_id'  => 'int',
				'message'    => 'string-65534',
				'created_at' => 'int'
			),
			array(
				$parent,
				$page_id,
				$user_info['id'],
				$message,
				$time = time()
			),
			array('id', 'page_id'),
			1
		);

		$smcFunc['lp_num_queries']++;

		if (!empty($item)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_comments = num_comments + 1
				WHERE page_id = {int:item}',
				array(
					'item' => $page_id
				)
			);

			$smcFunc['lp_num_queries']++;

			loadTemplate('LightPortal/ViewPage');

			ob_start();

			show_single_comment([
				'id'          => $item,
				'alias'       => $this->alias,
				'parent_id'   => $parent,
				'author_id'   => $user_info['id'],
				'author_name' => $user_info['name'],
				'avatar'      => $this->getUserAvatar($user_info['id']),
				'message'     => empty($context['lp_allowed_bbc']) ? $message : parse_bbc($message, true, 'light_portal_comments_' . $item, $context['lp_allowed_bbc']),
				'created_at'  => date('Y-m-d', $time),
				'created'     => Helpers::getFriendlyTime($time),
				'raw_message' => un_preparsecode($message),
				'can_edit'    => true
			], $counter + 1, $level + 1);

			$comment = ob_get_clean();

			$result = array(
				'item'        => $item,
				'parent'      => $parent,
				'comment'     => $comment,
				'created'     => $time,
				'title'       => $txt['response_prefix'] . $page_title,
				'alias'       => $this->alias,
				'page_url'    => $page_url,
				'start'       => $start,
				'commentator' => $commentator
			);

			empty($parent)
				? $this->makeNotify('new_comment', 'page_comment', $result)
				: $this->makeNotify('new_reply', 'page_comment_reply', $result);

			Helpers::cache()->forget('page_' . $this->alias . '_comments');
		}

		exit(json_encode($result));
	}

	/**
	 * Editing a comment
	 *
	 * Редактирование комментария
	 *
	 * @return void
	 */
	private function edit()
	{
		global $context, $smcFunc;

		$data = Helpers::request()->json();

		if (empty($data) || $context['user']['is_guest'])
			exit;

		$item    = $data['comment_id'];
		$message = Helpers::validate($data['message']);

		if (empty($item) || empty($message))
			exit;

		Helpers::require('Subs-Post');
		preparsecode($message);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_comments
			SET message = {string:message}
			WHERE id = {int:id}
				AND author_id = {int:user}',
			array(
				'message' => shorten_subject($message, 65531),
				'id'      => $item,
				'user'    => $context['user']['id']
			)
		);

		$smcFunc['lp_num_queries']++;

		$message = empty($context['lp_allowed_bbc']) ? $message : parse_bbc($message, true, 'light_portal_comments_' . $item, $context['lp_allowed_bbc']);

		Helpers::cache()->forget('page_' . $this->alias . '_comments');

		exit(json_encode($message));
	}

	/**
	 * Get user avatar image
	 *
	 * Получение аватарки пользователя
	 *
	 * @param $user_id
	 * @return string
	 */
	private function getUserAvatar($user_id)
	{
		global $memberContext;

		if (!isset($memberContext[$user_id])) {
			loadMemberData($user_id);
			loadMemberContext($user_id, true);
		}

		return $memberContext[$user_id]['avatar']['image'];
	}

	/**
	 * Creating a background task to notify subscribers of new comments
	 *
	 * Создаем фоновую задачу для уведомления подписчиков о новых комментариях
	 *
	 * @param string $type
	 * @param string $action
	 * @param array $options
	 * @return void
	 */
	private function makeNotify(string $type, string $action, array $options = [])
	{
		global $smcFunc, $user_info, $context;

		if (empty($options))
			return;

		$smcFunc['db_insert']('',
			'{db_prefix}background_tasks',
			array(
				'task_file'  => 'string',
				'task_class' => 'string',
				'task_data'  => 'string'
			),
			array(
				'task_file'  => '$sourcedir/LightPortal/Notify.php',
				'task_class' => Notify::class,
				'task_data'  => $smcFunc['json_encode']([
					'time'           => $options['created'],
					'sender_id'	     => $user_info['id'],
					'sender_name'    => $user_info['name'],
					'author_id'      => $context['lp_page']['author_id'],
					'commentator_id' => $options['commentator'],
					'content_type'   => $type,
					'content_id'     => $options['item'],
					'content_action' => $action,
					'extra'          => $smcFunc['json_encode']([
						'content_subject' => $options['title'],
						'content_link'    => $options['page_url'] . 'start=' . $options['start'] . '#comment' . $options['item']
					])
				]),
			),
			array('id_task')
		);

		$smcFunc['lp_num_queries']++;
	}

	/**
	 * Deleting a comment (and all children)
	 *
	 * Удаление комментария (и всех дочерних)
	 *
	 * @return void
	 */
	private function remove()
	{
		global $smcFunc;

		$items = Helpers::request()->json('items');

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET num_comments = CASE WHEN num_comments > 0 THEN num_comments - {int:num_items}
				ELSE num_comments END
			WHERE alias = {string:alias}',
			array(
				'num_items' => count($items),
				'alias'     => $this->alias
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}user_alerts
			WHERE content_type = {string:type}
				AND content_id IN ({array_int:items})',
			array(
				'type'  => 'new_comment',
				'items' => $items
			)
		);

		$smcFunc['lp_num_queries'] += 3;

		Helpers::cache()->forget('page_' . $this->alias . '_comments');

		exit;
	}

	/**
	 * Get all comments (or for the current page only)
	 *
	 * Получаем все комментарии (или только для конкретной страницы)
	 *
	 * @param int $page_id
	 * @return array
	 */
	public function getAll(int $page_id = 0)
	{
		global $smcFunc, $context, $modSettings;

		if (empty($page_id))
			return [];

		Helpers::require('Subs-Post');

		$request = $smcFunc['db_query']('', '
			SELECT com.id, com.parent_id, com.page_id, com.author_id, com.message, com.created_at, mem.real_name AS author_name
			FROM {db_prefix}lp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.author_id = mem.id_member)' . (!empty($page_id) ? '
			WHERE com.page_id = {int:id}' : ''),
			array(
				'id' => $page_id
			)
		);

		$comments = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['message']);

			$avatar = $this->getUserAvatar($row['author_id']);

			$comments[$row['id']] = array(
				'id'          => $row['id'],
				'page_id'     => $row['page_id'],
				'parent_id'   => $row['parent_id'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'avatar'      => $avatar,
				'message'     => empty($context['lp_allowed_bbc']) ? $row['message'] : parse_bbc($row['message'], true, 'light_portal_comments_' . $page_id, $context['lp_allowed_bbc']),
				'raw_message' => un_preparsecode($row['message']),
				'created_at'  => $row['created_at'],
				'can_edit'    => !empty($modSettings['lp_time_to_change_comments']) ? (time() - $row['created_at'] <= (int) $modSettings['lp_time_to_change_comments'] * 60) : false
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $comments;
	}

	/**
	 * Get comment tree (parents and children)
	 *
	 * Получаем дерево комментариев
	 *
	 * @param array $data
	 * @return array
	 */
	private function getTree(array $data)
	{
		$tree = [];

		foreach ($data as $id => &$node) {
			empty($node['parent_id'])
				? $tree[$id] = &$node
				: $data[$node['parent_id']]['children'][$id] = &$node;
		}

		return $tree;
	}
}
