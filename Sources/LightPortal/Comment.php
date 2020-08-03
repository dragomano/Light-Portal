<?php

namespace Bugo\LightPortal;

/**
 * Comment.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.0
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
		global $context, $txt, $modSettings, $scripturl;

		if (empty($this->alias))
			return;

		if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'new_comment') {
			$this->add();
		}

		if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'del_comment') {
			$this->remove();
		}

		$comments = Helpers::getFromCache('page_' . $this->alias . '_comments',	'getAll', __CLASS__, LP_CACHE_TIME, $context['lp_page']['id']);
		$comments = array_map(
			function ($comment) {
				$comment['created']    = Helpers::getFriendlyTime($comment['created_at']);
				$comment['created_at'] = date('Y-m-d', $comment['created_at']);

				return $comment;
			},
			$comments
		);

		$total_comments     = sizeof($comments);
		$txt['lp_comments'] = Helpers::getCorrectDeclension($total_comments, $txt['lp_comments_set']);

		$limit          = $modSettings['lp_num_comments_per_page'] ?? 10;
		$comment_tree   = $this->getTree($comments);
		$total_comments = sizeof($comment_tree);

		$page_index_url = $context['canonical_url'];
		if (!empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] == 1 && !empty($modSettings['lp_frontpage_alias']))
			$page_index_url = $scripturl . '?action=portal';

		$temp_start            = (int) $_REQUEST['start'];
		$context['page_index'] = constructPageIndex($page_index_url, $_REQUEST['start'], $total_comments, $limit);
		$context['start']      = &$_REQUEST['start'];
		$start                 = (int) $_REQUEST['start'];

		$context['page_info']['num_pages'] = floor(($total_comments - 1) / $limit) + 1;
		$context['page_info']['start']     = $context['page_info']['num_pages'] * $limit - $limit;

		if ($temp_start > $total_comments)
			send_http_status(404);

		$context['lp_page']['comments'] = array_slice($comment_tree, $start, $limit);
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
		global $smcFunc, $user_info, $context, $modSettings, $txt;

		$args = array(
			'parent_id'   => FILTER_VALIDATE_INT,
			'counter'     => FILTER_VALIDATE_INT,
			'level'       => FILTER_VALIDATE_INT,
			'page_id'     => FILTER_VALIDATE_INT,
			'page_title'  => FILTER_SANITIZE_STRING,
			'page_alias'  => FILTER_SANITIZE_STRING,
			'page_url'    => FILTER_SANITIZE_STRING,
			'message'     => FILTER_SANITIZE_STRING,
			'start'       => FILTER_VALIDATE_INT,
			'commentator' => FILTER_VALIDATE_INT
		);

		$data = filter_input_array(INPUT_POST, $args);

		if (empty($data))
			return;

		$parent      = $data['parent_id'];
		$counter     = $data['counter'];
		$level       = $data['level'];
		$page_id     = $data['page_id'];
		$page_title  = $data['page_title'];
		$page_alias  = $data['page_alias'];
		$page_url    = $data['page_url'];
		$message     = $data['message'];
		$start       = $data['start'];
		$commentator = $data['commentator'];

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
			array('id', 'page_id'),
			1
		);

		$context['lp_num_queries']++;

		$result['error'] = true;

		if (!empty($item)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_comments = num_comments + 1
				WHERE page_id = {int:item}',
				array(
					'item' => $page_id
				)
			);

			$context['lp_num_queries']++;

			loadTemplate('LightPortal/ViewPage');

			ob_start();

			$enabled_tags = !empty($modSettings['lp_enabled_bbc_in_comments']) ? explode(',', $modSettings['lp_enabled_bbc_in_comments']) : [];

			show_single_comment([
				'id'          => $item,
				'alias'       => $page_alias,
				'author_id'   => $user_info['id'],
				'author_name' => $user_info['name'],
				'avatar'      => $this->getUserAvatar(),
				'message'     => empty($enabled_tags) ? $message : parse_bbc($message, true, 'light_portal_comments_' . $item, $enabled_tags),
				'created_at'  => date('Y-m-d', $time),
				'created'     => Helpers::getFriendlyTime($time)
			], $counter + 1, $level + 1);

			$comment = ob_get_clean();

			$result = array(
				'item'        => $item,
				'parent'      => $parent,
				'comment'     => $comment,
				'created'     => $time,
				'title'       => $txt['response_prefix'] . $page_title,
				'alias'       => $page_alias,
				'page_url'    => $page_url,
				'start'       => $start,
				'commentator' => $commentator
			);

			if (empty($parent))
				$this->makeNotify('new_comment', 'page_comment', $result);
			else
				$this->makeNotify('new_reply', 'page_comment_reply', $result);

			Helpers::getFromCache('page_' . $page_alias . '_comments', null);
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
	private function getUserAvatar()
	{
		global $modSettings, $user_info, $smcFunc, $scripturl;

		$user_avatar = [];

		if ((!empty($modSettings['gravatarEnabled']) && substr($user_info['avatar']['url'], 0, 11) == 'gravatar://') || !empty($modSettings['gravatarOverride'])) {
			if (!empty($modSettings['gravatarAllowExtraEmail']) && stristr($user_info['avatar']['url'], 'gravatar://') && isset($user_info['avatar']['url'][12]))
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
			$user_avatar['image'] = '<img src="' . $user_avatar['href'] . '" alt="' . $user_info['name'] . '" class="avatar">';

		return $user_avatar['image'];
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
				'$sourcedir/LightPortal/Notify.php',
				'\Bugo\LightPortal\Notify',
				$smcFunc['json_encode'](
					array(
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
					)
				)
			),
			array('id_task')
		);

		$context['lp_num_queries']++;
	}

	/**
	 * Deleting a comment (and all childs)
	 *
	 * Удаление комментария (и всех дочерних)
	 *
	 * @return void
	 */
	private function remove()
	{
		global $smcFunc, $context;

		$json  = file_get_contents('php://input');
		$data  = json_decode($json, true);
		$items = $data['items'];

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

		$context['lp_num_queries'] += 2;

		Helpers::getFromCache('page_' . $this->alias . '_comments', null);

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
	public static function getAll(int $page_id = 0)
	{
		global $smcFunc, $memberContext, $modSettings, $context;

		if (empty($page_id))
			return [];

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

			if (!isset($memberContext[$row['author_id']])) {
				loadMemberData($row['author_id']);
				loadMemberContext($row['author_id']);
			}

			$avatar = $memberContext[$row['author_id']]['avatar']['image'];

			// Temporaly fix
			if (empty($modSettings['gravatarOverride']) && empty($modSettings['gravatarEnabled']) && stristr($memberContext[$row['author_id']]['avatar']['name'], 'gravatar://'))
				$avatar = '<img class="avatar" src="' . $modSettings['avatar_url'] . '/default.png" alt="">';

			$enabled_tags = !empty($modSettings['lp_enabled_bbc_in_comments']) ? explode(',', $modSettings['lp_enabled_bbc_in_comments']) : [];

			$comments[$row['id']] = array(
				'id'          => $row['id'],
				'page_id'     => $row['page_id'],
				'parent_id'   => $row['parent_id'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'avatar'      => $avatar,
				'message'     => empty($enabled_tags) ? $row['message'] : parse_bbc($row['message'], true, 'light_portal_comments_' . $page_id, $enabled_tags),
				'created_at'  => $row['created_at']
			);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

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
	private function getTree(array $data)
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
