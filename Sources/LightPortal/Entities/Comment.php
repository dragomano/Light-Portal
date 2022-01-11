<?php

declare(strict_types = 1);

/**
 * Comment.php
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
use function addInlineJavaScript;
use function censorText;
use function un_preparsecode;
use function loadMemberData;
use function preparsecode;
use function send_http_status;

if (! defined('SMF'))
	die('No direct access...');

final class Comment
{
	use Helper;

	private string $alias;

	public function __construct(string $alias = '')
	{
		$this->alias = $alias;
	}

	public function prepare()
	{
		if (empty($this->alias))
			return;

		$this->context['lp_allowed_bbc'] = empty($this->modSettings['lp_enabled_bbc_in_comments']) ? [] : explode(',', $this->modSettings['lp_enabled_bbc_in_comments']);
		$this->context['lp_allowed_bbc'] = array_diff($this->context['lp_allowed_bbc'], array_intersect(explode(',', $this->modSettings['disabledBBC']), $this->context['lp_allowed_bbc']));

		if ($this->request()->notEmpty('sa')) {
			switch ($this->request('sa')) {
				case 'add_comment':
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

		$comments = $this->cache('page_' . $this->alias . '_comments')->setFallback(__CLASS__, 'getAll', $this->context['lp_page']['id']);
		$comments = array_map(function ($comment) {
			$comment['created']    = $this->getFriendlyTime($comment['created_at']);
			$comment['created_at'] = date('Y-m-d', $comment['created_at']);

			return $comment;
		}, $comments);

		$this->txt['lp_comments'] = __('lp_comments_set', ['comments' => sizeof($comments)]);

		$limit = (int) $this->modSettings['lp_num_comments_per_page'] ?? 10;
		$commentTree = $this->getTree($comments);
		$totalParentComments = sizeof($commentTree);

		$this->context['current_start'] = $this->request('start');
		$this->context['page_index'] = constructPageIndex($this->getPageIndexUrl(), $this->request()->get('start'), $totalParentComments, $limit);
		$start = $this->request('start');

		$this->context['page_info'] = [
			'num_pages' => $num_pages = floor($totalParentComments / $limit) + 1,
			'start'     => $num_pages * $limit - $limit
		];

		if ($this->context['current_start'] > $totalParentComments)
			send_http_status(404);

		$this->context['lp_page']['comments'] = array_slice($commentTree, $start, $limit);

		if ($this->context['user']['is_logged']) {
			addInlineJavaScript('
		const comment = new Comment({
			pageUrl: "' . $this->context['canonical_url'] . ($this->request()->has(LP_PAGE_PARAM) ? ';' : '?') . '",
			start: ' . $start . ',
			lastStart: ' . $this->context['page_info']['start'] . ',
			totalParentComments: ' . count($this->context['lp_page']['comments']) . ',
			commentsPerPage: ' . $limit . '
		});
		const toolbar = new Toolbar();');
		}
	}

	public function getAll(int $page_id = 0): array
	{
		$this->require('Subs-Post');

		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT com.id, com.parent_id, com.page_id, com.author_id, com.message, com.created_at, mem.real_name AS author_name
			FROM {db_prefix}lp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.author_id = mem.id_member)' . ($page_id ? '
			WHERE com.page_id = {int:id}' : ''),
			[
				'id' => $page_id
			]
		);

		$comments = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			censorText($row['message']);

			$comments[$row['id']] = [
				'id'          => (int) $row['id'],
				'page_id'     => (int) $row['page_id'],
				'parent_id'   => (int) $row['parent_id'],
				'poster'      => [
					'id'   => (int) $row['author_id'],
					'name' => $row['author_name']
				],
				'message'     => empty($this->context['lp_allowed_bbc']) ? $row['message'] : parse_bbc($row['message'], true, 'lp_comments_' . $page_id, $this->context['lp_allowed_bbc']),
				'raw_message' => un_preparsecode($row['message']),
				'created_at'  => (int) $row['created_at'],
				'can_edit'    => $this->modSettings['lp_time_to_change_comments'] && time() - $row['created_at'] <= (int) $this->modSettings['lp_time_to_change_comments'] * 60
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $this->getCommentsWithUserAvatars($comments);
	}

	private function add()
	{
		$result['error'] = true;

		if (empty($this->user_info['id']))
			exit(json_encode($result));

		$data = $this->request()->json();

		if (empty($data['message']))
			exit(json_encode($result));

		$parent      = filter_var($data['parent_id'], FILTER_VALIDATE_INT);
		$counter     = filter_var($data['counter'], FILTER_VALIDATE_INT);
		$level       = filter_var($data['level'], FILTER_VALIDATE_INT);
		$page_id     = filter_var($data['page_id'], FILTER_VALIDATE_INT);
		$page_url    = filter_var($data['page_url'], FILTER_SANITIZE_STRING);
		$message     = $this->smcFunc['htmlspecialchars']($data['message'], ENT_QUOTES);
		$start       = filter_var($data['start'], FILTER_VALIDATE_INT);
		$commentator = filter_var($data['commentator'], FILTER_VALIDATE_INT);

		if (empty($page_id) || empty($message))
			exit(json_encode($result));

		$this->require('Subs-Post');

		preparsecode($message);

		$item = $this->smcFunc['db_insert']('',
			'{db_prefix}lp_comments',
			[
				'parent_id'  => 'int',
				'page_id'    => 'int',
				'author_id'  => 'int',
				'message'    => 'string-65534',
				'created_at' => 'int'
			],
			[
				$parent,
				$page_id,
				$this->user_info['id'],
				$message,
				$time = time()
			],
			['id', 'page_id'],
			1
		);

		$this->context['lp_num_queries']++;

		if ($item) {
			$this->smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_comments = num_comments + 1
				WHERE page_id = {int:item}',
				[
					'item' => $page_id
				]
			);

			$this->context['lp_num_queries']++;

			ob_start();

			show_single_comment([
				'id'          => $item,
				'start'       => $start,
				'parent_id'   => $parent,
				'poster'      => [
					'id'     => $this->user_info['id'],
					'name'   => $this->user_info['name'],
					'avatar' => $this->getUserAvatar($this->user_info['id']),
				],
				'message'     => empty($this->context['lp_allowed_bbc']) ? $message : parse_bbc($message, true, 'lp_comments_' . $item, $this->context['lp_allowed_bbc']),
				'created_at'  => date('Y-m-d', $time),
				'created'     => $this->getFriendlyTime($time),
				'raw_message' => un_preparsecode($message),
				'can_edit'    => true
			], $counter + 1, $level + 1);

			$comment = ob_get_clean();

			$result = [
				'item'        => $item,
				'parent'      => $parent,
				'comment'     => $comment,
				'created'     => $time,
				'title'       => $this->txt['response_prefix'] . $this->context['page_title'],
				'alias'       => $this->alias,
				'page_url'    => $page_url,
				'start'       => $start,
				'commentator' => $commentator
			];

			empty($parent)
				? $this->makeNotify('new_comment', 'page_comment', $result)
				: $this->makeNotify('new_reply', 'page_comment_reply', $result);

			$this->cache()->forget('page_' . $this->alias . '_comments');
		}

		exit(json_encode($result));
	}

	private function edit()
	{
		$data = $this->request()->json();

		if (empty($data) || $this->context['user']['is_guest'])
			exit;

		$item    = $data['comment_id'];
		$message = $this->validate($data['message']);

		if (empty($item) || empty($message))
			exit;

		$this->require('Subs-Post');
		preparsecode($message);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_comments
			SET message = {string:message}
			WHERE id = {int:id}
				AND author_id = {int:user}',
			[
				'message' => shorten_subject($message, 65531),
				'id'      => $item,
				'user'    => $this->context['user']['id']
			]
		);

		$this->context['lp_num_queries']++;

		$message = empty($this->context['lp_allowed_bbc']) ? $message : parse_bbc($message, true, 'lp_comments_' . $item, $this->context['lp_allowed_bbc']);

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit(json_encode($message));
	}

	private function remove()
	{
		$items = $this->request()->json('items');

		if (empty($items))
			return;

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET num_comments = CASE WHEN num_comments - {int:num_items} >= 0 THEN num_comments - {int:num_items}
				ELSE num_comments END
			WHERE alias = {string:alias}',
			[
				'num_items' => count($items),
				'alias'     => $this->alias
			]
		);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}user_alerts
			WHERE content_type = {string:type}
				AND content_id IN ({array_int:items})',
			[
				'type'  => 'new_comment',
				'items' => $items
			]
		);

		$this->context['lp_num_queries'] += 3;

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit;
	}

	/**
	 * Creating a background task to notify subscribers of new comments
	 *
	 * Создаем фоновую задачу для уведомления подписчиков о новых комментариях
	 */
	private function makeNotify(string $type, string $action, array $options = [])
	{
		if (empty($options))
			return;

		$this->smcFunc['db_insert']('',
			'{db_prefix}background_tasks',
			[
				'task_file'  => 'string',
				'task_class' => 'string',
				'task_data'  => 'string'
			],
			[
				'task_file'  => '$sourcedir/LightPortal/Tasks/Notifier.php',
				'task_class' => '\Bugo\LightPortal\Tasks\Notifier',
				'task_data'  => $this->smcFunc['json_encode']([
					'time'           => $options['created'],
					'sender_id'	     => $this->user_info['id'],
					'sender_name'    => $this->user_info['name'],
					'author_id'      => $this->context['lp_page']['author_id'],
					'commentator_id' => $options['commentator'],
					'content_type'   => $type,
					'content_id'     => $options['item'],
					'content_action' => $action,
					'extra'          => $this->smcFunc['json_encode']([
						'content_subject' => $options['title'],
						'content_link'    => $options['page_url'] . 'start=' . $options['start'] . '#comment' . $options['item'],
                        'sender_gender'   => $this->user_profile[$this->user_info['id']]['options']['cust_gender'] === 'Female' ? 'female' : 'male'
					])
				]),
			],
			['id_task']
		);

		$this->context['lp_num_queries']++;
	}

	private function getTree(array $data): array
	{
		$tree = [];

		foreach ($data as $id => &$node) {
			empty($node['parent_id'])
				? $tree[$id] = &$node
				: $data[$node['parent_id']]['children'][$id] = &$node;
		}

		return $tree;
	}

	private function getPageIndexUrl(): string
	{
		if (! (empty($this->modSettings['lp_frontpage_mode']) || $this->modSettings['lp_frontpage_mode'] !== 'chosen_page') && ! empty($this->modSettings['lp_frontpage_alias']))
			return LP_BASE_URL;

		return $this->context['canonical_url'];
	}

	private function getCommentsWithUserAvatars(array $comments): array
	{
		$userData = loadMemberData(array_map(fn($item) => $item['poster']['id'], $comments));

		return array_map(function ($item) use ($userData) {
			if ($item['poster']['id'] && in_array($item['poster']['id'], $userData)) {
				if (! isset($this->memberContext[$item['poster']['id']]))
					try {
						loadMemberContext($item['poster']['id']);
					} catch (\Exception $e) {
						log_error('[LP] Comments (page #' . $item['page_id'] . ', user #' . $item['poster']['id'] . '): ' . $e->getMessage(), 'user');
					}

				$item['poster']['avatar'] = $this->memberContext[$item['poster']['id']]['avatar']['image'];
			} else {
				$item['poster']['avatar'] = '<img class="avatar" src="' . $this->modSettings['avatar_url'] . '/default.png" loading="lazy" alt="' . $item['poster']['name'] . '">';
			}

			return $item;
		}, $comments);
	}
}
