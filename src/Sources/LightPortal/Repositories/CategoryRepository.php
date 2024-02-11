<?php declare(strict_types=1);

/**
 * CategoryRepository.php
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

use Bugo\Compat\{Database as Db, Utils};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryRepository
{
	use Helper;

	public function add(string $name, string $desc): int
	{
		$name = Utils::$smcFunc['htmlspecialchars']($name);
		$desc = Utils::$smcFunc['htmlspecialchars']($desc);

		$item = (int) Db::$db->insert('',
			'{db_prefix}lp_categories',
			[
				'name'        => 'string',
				'description' => 'string',
				'priority'    => 'int'
			],
			[
				$name,
				$desc,
				$this->getPriority()
			],
			['category_id'],
			1
		);

		Utils::$context['lp_num_queries']++;

		return $item;
	}

	public function updatePriority(array $categories): void
	{
		if (empty($categories))
			return;

		$conditions = '';
		foreach ($categories as $priority => $item) {
			$conditions .= ' WHEN category_id = ' . $item . ' THEN ' . $priority;
		}

		if (empty($conditions))
			return;

		Db::$db->query('', /** @lang text */ '
			UPDATE {db_prefix}lp_categories
			SET priority = CASE ' . $conditions . ' ELSE priority END
			WHERE category_id IN ({array_int:categories})',
			[
				'categories' => $categories
			]
		);

		Utils::$context['lp_num_queries']++;

		$result = [
			'success' => Db::$db->affected_rows()
		];

		$this->cache()->forget('all_categories');

		exit(json_encode($result));
	}

	public function updateName(int $item, string $value): void
	{
		if (empty($item))
			return;

		Db::$db->query('', '
			UPDATE {db_prefix}lp_categories
			SET name = {string:name}
			WHERE category_id = {int:item}',
			[
				'name' => Utils::$smcFunc['htmlspecialchars']($value),
				'item' => $item
			]
		);

		Utils::$context['lp_num_queries']++;

		$result = [
			'success' => Db::$db->affected_rows()
		];

		exit(json_encode($result));
	}

	public function updateDescription(int $item, string $value): void
	{
		if (empty($item))
			return;

		Db::$db->query('', '
			UPDATE {db_prefix}lp_categories
			SET description = {string:desc}
			WHERE category_id = {int:item}',
			[
				'desc' => Utils::$smcFunc['htmlspecialchars']($value),
				'item' => $item
			]
		);

		Utils::$context['lp_num_queries']++;

		$result = [
			'success' => Db::$db->affected_rows()
		];

		exit(json_encode($result));
	}

	public function remove(array $items): void
	{
		if (empty($items))
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_categories
			WHERE category_id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		$result = [
			'success' => Db::$db->affected_rows()
		];

		Db::$db->query('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category}
			WHERE category_id IN ({array_int:items})',
			[
				'category' => 0,
				'items'    => $items
			]
		);

		Utils::$context['lp_num_queries'] += 2;

		$this->cache()->flush();

		exit(json_encode($result));
	}

	private function getPriority(): int
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_categories',
			[]
		);

		[$priority] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return (int) $priority;
	}
}
