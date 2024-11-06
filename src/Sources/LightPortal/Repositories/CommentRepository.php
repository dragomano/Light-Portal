<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Config, Db, Lang};
use Bugo\LightPortal\Utils\Avatar;

use function array_column;
use function count;
use function htmlspecialchars_decode;
use function time;

if (! defined('SMF'))
	die('No direct access...');

final class CommentRepository
{
	public function getAll(): array
	{
		return $this->getByPageId();
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
				'message'     => htmlspecialchars_decode((string) $row['message']),
				'created_at'  => (int) $row['created_at'],
				'can_edit'    => $this->isCanEdit((int) $row['created_at']),
				'poster'      => [
					'id'   => (int) $row['author_id'],
					'name' => $row['author_name'],
				],
			];

			if (isset($row['name'])) {
				$comments[$row['id']]['params'][$row['name']] = $row['value'];
			}
		}

		Db::$db->free_result($result);

		return Avatar::getWithItems($comments, 'poster');
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
	}

	public function remove(int $item, string $pageSlug): array
	{
		$result = Db::$db->query('', '
			SELECT id
			FROM {db_prefix}lp_comments
			WHERE id = {int:item}
				OR parent_id = {int:item}',
			[
				'item' => $item,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[] = (int) $row['id'];
		}

		Db::$db->free_result($result);

		if ($items === []) {
			return [];
		}

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
			WHERE slug = {string:slug}
				AND num_comments - {int:num_items} >= 0',
			[
				'num_items' => count($items),
				'slug'      => $pageSlug,
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
				WHERE p.slug = {string:slug}
			)
			WHERE slug = {string:slug}',
			[
				'slug' => $pageSlug,
			]
		);

		return $items;
	}

	public function removeFromResult(object|bool $result): void
	{
		$comments = Db::$db->fetch_all($result);
		$comments = array_column($comments, 'id');

		Db::$db->free_result($result);

		if ($comments === [])
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE id IN ({array_int:items})',
			[
				'items' => $comments,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:comment}',
			[
				'items' => $comments,
			]
		);
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
	}

	private function isCanEdit(int $date): bool
	{
		if (empty($timeToChange = (int) (Config::$modSettings['lp_time_to_change_comments'] ?? 0)))
			return false;

		return $timeToChange && time() - $date <= $timeToChange * 60;
	}
}
