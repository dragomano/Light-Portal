<?php

declare(strict_types=1);

/**
 * CommentRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Config, Database as Db, Lang, Utils};
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
		$result = Db::$db->query('', '
			SELECT *
			FROM {db_prefix}lp_comments
			WHERE id = {int:id}',
			[
				'id' => $id,
			]
		);

		$data = Db::$db->fetch_assoc($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $data ?? [];
	}

	public function getByPageId(int $id = 0): array
	{
		$sorts = [
			'com.created_at',
			'com.created_at DESC',
		];

		$result = Db::$db->query('', /** @lang text */ '
			SELECT com.id, com.parent_id, com.page_id, com.author_id, com.message, com.created_at,
				mem.real_name AS author_name, par.name, par.value
			FROM {db_prefix}lp_comments AS com
				INNER JOIN {db_prefix}members AS mem ON (com.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_params AS par ON (
					com.id = par.item_id AND par.type = {literal:comment}
				)' . ($id ? '
			WHERE com.page_id = {int:id}' : '') . '
			ORDER BY ' . $sorts[Config::$modSettings['lp_comment_sorting'] ?? 0],
			[
				'id' => $id,
			]
		);

		$comments = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['message']);

			$comments[$row['id']] = [
				'id'          => (int) $row['id'],
				'page_id'     => (int) $row['page_id'],
				'parent_id'   => (int) $row['parent_id'],
				'message'     => htmlspecialchars_decode($row['message']),
				'created_at'  => (int) $row['created_at'],
				'can_edit'    => $this->isCanEdit((int) $row['created_at']),
				'poster'      => [
					'id'   => (int) $row['author_id'],
					'name' => $row['author_name'],
				],
			];

			if (isset($row['name']))
				$comments[$row['id']]['params'][$row['name']] = $row['value'];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $this->getItemsWithUserAvatars($comments, 'poster');
	}

	public function save(array $data): int
	{
		$item = Db::$db->insert('',
			'{db_prefix}lp_comments',
			[
				'parent_id'  => 'int',
				'page_id'    => 'int',
				'author_id'  => 'int',
				'message'    => 'string-65534',
				'created_at' => 'int',
			],
			$data,
			['id', 'page_id'],
			1
		);

		Utils::$context['lp_num_queries']++;

		return (int) $item;
	}

	public function update(array $data): void
	{
		Db::$db->query('', '
			UPDATE {db_prefix}lp_comments
			SET message = {string:message}
			WHERE id = {int:id}
				AND author_id = {int:user}',
			$data
		);

		Utils::$context['lp_num_queries']++;
	}

	public function remove(array $items, string $pageAlias): void
	{
		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->db_query('', '
			UPDATE {db_prefix}lp_pages
			SET num_comments = num_comments - {int:num_items}
			WHERE alias = {string:alias}
				AND num_comments - {int:num_items} >= 0',
			[
				'num_items' => count($items),
				'alias'     => $pageAlias,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:comment}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}user_alerts
			WHERE content_type = {string:type}
				AND content_id IN ({array_int:items})',
			[
				'type'  => 'new_comment',
				'items' => $items,
			]
		);

		Db::$db->query('', '
			UPDATE {db_prefix}lp_pages
			SET last_comment_id = (
				SELECT COALESCE(MAX(com.id), 0)
				FROM {db_prefix}lp_comments AS com
					LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = com.page_id)
				WHERE p.alias = {string:alias}
			)
			WHERE alias = {string:alias}',
			[
				'alias' => $pageAlias,
			]
		);

		Utils::$context['lp_num_queries'] += 5;
	}

	public function updateLastCommentId(int $item, int $pageId): void
	{
		Db::$db->query('', '
			UPDATE {db_prefix}lp_pages
			SET num_comments = num_comments + 1, last_comment_id = {int:item}
			WHERE page_id = {int:page_id}',
			[
				'item'    => $item,
				'page_id' => $pageId,
			]
		);

		Utils::$context['lp_num_queries']++;
	}

	private function isCanEdit(int $date): bool
	{
		if (empty($timeToChange = (int) (Config::$modSettings['lp_time_to_change_comments'] ?? 0)))
			return false;

		return $timeToChange && time() - $date <= $timeToChange * 60;
	}
}
