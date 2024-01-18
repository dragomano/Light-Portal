<?php declare(strict_types=1);

/**
 * CategoryConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Utils\{Lang, Theme, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class CategoryConfig extends AbstractConfig
{
	public function show(): void
	{
		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['sub_template'] = 'lp_category_settings';

		Utils::$context['page_title'] = Lang::$txt['lp_categories'];

		Utils::$context['lp_categories'] = $this->getEntityList('category');

		unset(Utils::$context['lp_categories'][0]);

		if ($this->request()->has('actions')) {
			$data = $this->request()->json();

			if (isset($data['new_name']))
				$this->add($data['new_name'], $data['new_desc'] ?? '');

			if (isset($data['update_priority']))
				$this->updatePriority($data['update_priority']);

			if (isset($data['name']))
				$this->updateName((int) $data['item'], $data['name']);

			if (isset($data['desc']))
				$this->updateDescription((int) $data['item'], $data['desc']);

			if (isset($data['del_item']))
				$this->remove([(int) $data['del_item']]);

			exit;
		}
	}

	private function add(string $name, string $desc = ''): void
	{
		if (empty($name))
			return;

		Theme::loadTemplate('LightPortal/ManageSettings');

		$result = [
			'error' => true
		];

		$name = Utils::$smcFunc['htmlspecialchars']($name);
		$desc = Utils::$smcFunc['htmlspecialchars']($desc);

		$item = (int) Utils::$smcFunc['db_insert']('',
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

		if ($item) {
			ob_start();

			show_single_category($item, ['name' => $name, 'desc' => $desc]);

			$new_cat = ob_get_clean();

			$result = [
				'success' => true,
				'section' => $new_cat,
				'item'    => $item
			];
		}

		$this->cache()->forget('all_categories');

		exit(json_encode($result));
	}

	private function updatePriority(array $categories): void
	{
		if (empty($categories))
			return;

		$conditions = '';
		foreach ($categories as $priority => $item) {
			$conditions .= ' WHEN category_id = ' . $item . ' THEN ' . $priority;
		}

		if (empty($conditions))
			return;

		Utils::$smcFunc['db_query']('', /** @lang text */ '
			UPDATE {db_prefix}lp_categories
			SET priority = CASE ' . $conditions . ' ELSE priority END
			WHERE category_id IN ({array_int:categories})',
			[
				'categories' => $categories
			]
		);

		Utils::$context['lp_num_queries']++;

		$result = [
			'success' => Utils::$smcFunc['db_affected_rows']()
		];

		$this->cache()->forget('all_categories');

		exit(json_encode($result));
	}

	private function updateName(int $item, string $value): void
	{
		if (empty($item))
			return;

		Utils::$smcFunc['db_query']('', '
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
			'success' => Utils::$smcFunc['db_affected_rows']()
		];

		exit(json_encode($result));
	}

	private function updateDescription(int $item, string $value): void
	{
		if (empty($item))
			return;

		Utils::$smcFunc['db_query']('', '
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
			'success' => Utils::$smcFunc['db_affected_rows']()
		];

		exit(json_encode($result));
	}

	private function remove(array $items): void
	{
		if (empty($items))
			return;

		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_categories
			WHERE category_id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		$result = [
			'success' => Utils::$smcFunc['db_affected_rows']()
		];

		Utils::$smcFunc['db_query']('', '
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
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_categories',
			[]
		);

		[$priority] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $priority;
	}
}
