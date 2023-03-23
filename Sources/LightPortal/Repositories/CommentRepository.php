<?php

declare(strict_types=1);

/**
 * CommentRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class CommentRepository
{
	use Helper;

	public function getAll(): array
	{
		return $this->getByPageId();
	}

	public function getById(int $id): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}lp_comments
			WHERE id = {int:id}',
			[
				'id' => $id,
			]
		);

		$data = $this->smcFunc['db_fetch_assoc']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $data ?? [];
	}

	public function getByPageId(int $page_id = 0): array
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

	/**
	 * @var int $data['parent_id']
	 * @var int $data['page_id']
	 * @var int $data['author_id']
	 * @var string $data['message']
	 * @var int $data['created_at']
	 */
	public function save(array $data): int
	{
		$item = $this->smcFunc['db_insert']('',
			'{db_prefix}lp_comments',
			[
				'parent_id'  => 'int',
				'page_id'    => 'int',
				'author_id'  => 'int',
				'message'    => 'string-65534',
				'created_at' => 'int'
			],
			$data,
			['id', 'page_id'],
			1
		);

		$this->context['lp_num_queries']++;

		return (int) $item;
	}

	/**
	 * @var string $data['message']
	 * @var int $data['id']
	 * @var int $data['user']
	 */
	public function update(array $data): void
	{
		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_comments
			SET message = {string:message}
			WHERE id = {int:id}
				AND author_id = {int:user}',
			$data
		);

		$this->context['lp_num_queries']++;
	}

	public function remove(array $items, string $page_alias): void
	{
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
				'alias'     => $page_alias
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
				'alias' => $page_alias
			]
		);

		$this->context['lp_num_queries'] += 5;
	}

	public function updateRating(int $item, string $operand): void
	{
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
	}

	public function updateLastCommentId(int $item, int $page_id): void
	{
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
	}

	private function isCanEdit(int $date): bool
	{
		if (empty($time_to_change = (int) ($this->modSettings['lp_time_to_change_comments']) ?? 0))
			return false;

		return $time_to_change && time() - $date <= $time_to_change * 60;
	}
}
