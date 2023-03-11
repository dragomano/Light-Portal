<?php declare(strict_types=1);

/**
 * Comment.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Helper;
use JetBrains\PhpStorm\NoReturn;

if (! defined('SMF'))
	die('No direct access...');

final class Comment
{
	use Helper;

	public function __construct(private string $alias = '') {}

	public function prepare(): void
	{
		if (empty($this->alias))
			return;

		$disabledBbc = isset($this->modSettings['disabledBBC']) ? explode(',', $this->modSettings['disabledBBC']) : [];
		$this->context['lp_allowed_bbc'] = empty($this->modSettings['lp_enabled_bbc_in_comments']) ? [] : explode(',', $this->modSettings['lp_enabled_bbc_in_comments']);
		$this->context['lp_allowed_bbc'] = array_diff($this->context['lp_allowed_bbc'], array_intersect($disabledBbc, $this->context['lp_allowed_bbc']));

		if ($this->request()->isNotEmpty('sa')) {
			switch ($this->request('sa')) {
				case 'add_comment':
					$this->add();
				case 'edit_comment':
					$this->edit();
				case 'like_comment':
					$this->like();
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

		$this->txt['lp_comments'] = $this->translate('lp_comments_set', ['comments' => sizeof($comments)]);

		$limit = (int) ($this->modSettings['lp_num_comments_per_page'] ?? 10);
		$commentTree = $this->getTree($comments);
		$totalParentComments = sizeof($commentTree);

		$this->context['current_start'] = $this->request('start');
		$this->context['page_index'] = $this->constructPageIndex($this->getPageIndexUrl(), $this->request()->get('start'), $totalParentComments, $limit);
		$start = $this->request('start');

		$this->context['page_info'] = [
			'num_pages' => $num_pages = floor($totalParentComments / $limit) + 1,
			'start'     => $num_pages * $limit - $limit
		];

		if ($this->context['current_start'] > $totalParentComments)
			$this->sendStatus(404);

		$this->context['lp_page']['comments'] = array_slice($commentTree, $start, $limit);

		if ($this->context['user']['is_logged']) {
			$this->addInlineJavaScript('
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
		$sorts = [
			'com.created_at',
			'com.created_at DESC',
			'CASE WHEN rating < 0 THEN -1 WHEN rating > 0 THEN 1 ELSE 0 END DESC',
		];

		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT com.id, com.parent_id, com.page_id, com.author_id, com.message, com.created_at, mem.real_name AS author_name,
				(SELECT SUM(r.value) FROM {db_prefix}lp_ratings AS r WHERE com.id = r.content_id) AS rating,
				(SELECT COUNT(r.id) FROM {db_prefix}lp_ratings AS r WHERE com.id = r.content_id AND r.user_id = {int:user}) AS is_rated
			FROM {db_prefix}lp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.author_id = mem.id_member)' . ($page_id ? '
			WHERE com.page_id = {int:id}' : '') . '
			ORDER BY ' . $sorts[$this->modSettings['lp_comment_sorting'] ?? 0],
			[
				'id'   => $page_id,
				'user' => $this->context['user']['id']
			]
		);

		$comments = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$this->censorText($row['message']);

			$comments[$row['id']] = [
				'id'           => (int) $row['id'],
				'page_id'      => (int) $row['page_id'],
				'parent_id'    => (int) $row['parent_id'],
				'poster'       => [
					'id'   => (int) $row['author_id'],
					'name' => $row['author_name']
				],
				'message'      => empty($this->context['lp_allowed_bbc']) ? $row['message'] : $this->parseBbc($row['message'], true, 'lp_comments_' . $page_id, $this->context['lp_allowed_bbc']),
				'raw_message'  => $this->unPreparseCode($row['message']),
				'created_at'   => (int) $row['created_at'],
				'rating'       => (int) $row['rating'],
				'is_rated'     => (bool) $row['is_rated'],
				'can_rate'     => $row['author_id'] != $this->context['user']['id'] && empty($this->context['user']['is_guest']),
				'rating_class' => empty($this->modSettings['lp_allow_comment_ratings']) ? '' : ($row['rating'] && $row['rating'] <= -10 ? 'negative' : ($row['rating'] >= 10 ? 'positive' : '')),
				'can_edit'     => $this->isCanEdit((int) $row['created_at'])
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $this->getItemsWithUserAvatars($comments, 'poster');
	}

	private function isCanEdit(int $date): bool
	{
		if (empty($this->modSettings['lp_time_to_change_comments']))
			return false;

		$time_to_change = (int) $this->modSettings['lp_time_to_change_comments'];

		return $time_to_change && time() - $date <= $time_to_change * 60;
	}

	#[NoReturn] private function add(): void
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
		$page_url    = filter_var($data['page_url'], FILTER_VALIDATE_URL);
		$message     = filter_var($data['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$start       = filter_var($data['start'], FILTER_VALIDATE_INT);
		$commentator = filter_var($data['commentator'], FILTER_VALIDATE_INT);

		if (empty($page_id) || empty($message))
			exit(json_encode($result));

		$this->preparseCode($message);

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
				SET num_comments = num_comments + 1, last_comment_id = {int:item}
				WHERE page_id = {int:page_id}',
				[
					'item'    => $item,
					'page_id' => $page_id
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
				'message'     => empty($this->context['lp_allowed_bbc']) ? $message : $this->parseBbc($message, true, 'lp_comments_' . $item, $this->context['lp_allowed_bbc']),
				'created_at'  => date('Y-m-d', $time),
				'created'     => $this->getFriendlyTime($time),
				'raw_message' => $this->unPreparseCode($message),
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

	#[NoReturn] private function edit(): void
	{
		$data = $this->request()->json();

		if (empty($data) || $this->context['user']['is_guest'])
			exit;

		$item    = $data['comment_id'];
		$message = $this->validate($data['message']);

		if (empty($item) || empty($message))
			exit;

		$this->preparseCode($message);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_comments
			SET message = {string:message}
			WHERE id = {int:id}
				AND author_id = {int:user}',
			[
				'message' => $this->getShortenText($message, 65531),
				'id'      => $item,
				'user'    => $this->context['user']['id']
			]
		);

		$this->context['lp_num_queries']++;

		$message = empty($this->context['lp_allowed_bbc']) ? $message : $this->parseBbc($message, true, 'lp_comments_' . $item, $this->context['lp_allowed_bbc']);

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit(json_encode($message));
	}

	private function like(): void
	{
		if (empty($this->modSettings['lp_allow_comment_ratings']))
			return;

		$data = $this->request()->json();

		if (empty($data) || $this->context['user']['is_guest'])
			exit;

		$item    = $data['comment_id'];
		$operand = $data['operand'];

		if (empty($item) || empty($operand))
			exit;

		if ($operand === '!') {
			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_ratings
				WHERE content_id = {int:item}
					AND user_id = {int:user}',
				[
					'item' => $item,
					'user' => $this->user_info['id']
				]
			);
		} else {
			$this->smcFunc['db_insert']('',
				'{db_prefix}lp_ratings',
				[
					'value'      => 'int',
					'content_id' => 'int',
					'user_id'    => 'int'
				],
				[
					$operand === '+' ? 1 : -1,
					$item,
					$this->user_info['id']
				],
				['id']
			);
		}

		$this->context['lp_num_queries']++;

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit;
	}

	private function remove(): void
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
			DELETE FROM {db_prefix}lp_ratings
			WHERE content_type = {string:type}
				AND content_id IN ({array_int:items})',
			[
				'type'  => 'comment',
				'items' => $items
			]
		);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET num_comments = num_comments - {int:num_items}
			WHERE alias = {string:alias}
				AND num_comments - {int:num_items} >= 0',
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

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET last_comment_id = (
				SELECT COALESCE(MAX(com.id), 0)
				FROM {db_prefix}lp_comments AS com
					LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = com.page_id)
				WHERE p.alias = {string:alias}
			)
			WHERE alias = {string:alias}',
			[
				'alias' => $this->alias
			]
		);

		$this->context['lp_num_queries'] += 5;

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit;
	}

	/**
	 * Creating a background task to notify subscribers of new comments
	 *
	 * Создаем фоновую задачу для уведомления подписчиков о новых комментариях
	 */
	private function makeNotify(string $type, string $action, array $options = []): void
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
						'sender_gender'   => strtolower($this->user_profile[$this->user_info['id']]['options']['cust_gender'] ?? 'male')
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
}
