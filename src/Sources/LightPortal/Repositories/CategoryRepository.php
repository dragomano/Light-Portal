<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Icon;

use function array_merge;
use function compact;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryRepository extends AbstractRepository
{
	protected string $entity = 'category';

	public function getAll(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT
				c.*,
				COALESCE(t.title, tf.title, {string:empty_string}) AS title,
				COALESCE(t.description, tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_categories AS c
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					c.category_id = tf.item_id AND tf.type = {literal:category} AND tf.lang = {string:fallback_lang}
				)
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($this->getLangQueryParams(), [
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);
			Lang::censorText($row['description']);

			$items[$row['category_id']] = [
				'id'          => (int) $row['category_id'],
				'slug'        => $row['slug'],
				'icon'        => Icon::parse($row['icon']),
				'priority'    => (int) $row['priority'],
				'status'      => (int) $row['status'],
				'title'       => $row['title'],
				'description' => $row['description'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(category_id)
			FROM {db_prefix}lp_categories',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function getData(int $item): array
	{
		if ($item === 0)
			return [];

		$result = Db::$db->query('
			SELECT
				c.*,
				COALESCE(t.title, {string:empty_string}) AS title,
				COALESCE(t.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_categories AS c
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
				)
			WHERE c.category_id = {int:item}',
			array_merge($this->getLangQueryParams(), compact('item'))
		);

		if (empty(Db::$db->num_rows($result))) {
			Utils::$context['error_link'] = Config::$scripturl . '?action=admin;area=lp_categories';

			ErrorHandler::fatalLang('lp_category_not_found', false, status: 404);
		}

		while ($row = Db::$db->fetch_assoc($result)) {
			$data ??= [
				'id'          => (int) $row['category_id'],
				'slug'        => $row['slug'],
				'icon'        => $row['icon'],
				'priority'    => (int) $row['priority'],
				'status'      => (int) $row['status'],
				'title'       => $row['title'],
				'description' => $row['description'],
			];
		}

		Db::$db->free_result($result);

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit'])) {
			return;
		}

		Security::checkSubmitOnce('check');

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		$this->session('lp')->free('active_categories');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_categories;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_categories;sa=edit;id=' . $item);
		}
	}

	public function remove(array $items): void
	{
		if ($items === [])
			return;

		Db::$db->query('
			DELETE FROM {db_prefix}lp_categories
			WHERE category_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->query('
			DELETE FROM {db_prefix}lp_translations
			WHERE item_id IN ({array_int:items})
				AND type = {literal:category}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category}
			WHERE category_id IN ({array_int:items})',
			[
				'category' => 0,
				'items'    => $items,
			]
		);

		$this->cache()->flush();

		$this->session('lp')->free('active_categories');
	}

	public function updatePriority(array $categories = []): void
	{
		if ($categories === [])
			return;

		$conditions = '';
		foreach ($categories as $priority => $item) {
			$conditions .= ' WHEN category_id = ' . $item . ' THEN ' . $priority;
		}

		if ($conditions === '')
			return;

		Db::$db->query(/** @lang text */ '
			UPDATE {db_prefix}lp_categories
			SET priority = CASE ' . $conditions . ' ELSE priority END
			WHERE category_id IN ({array_int:categories})',
			[
				'categories' => $categories,
			]
		);

		$this->cache()->forget('all_categories');
	}

	private function getPriority(): int
	{
		$result = Db::$db->query(/** @lang text */ '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_categories',
		);

		[$priority] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $priority;
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
			'{db_prefix}lp_categories',
			[
				'slug'     => 'string',
				'icon'     => 'string-60',
				'priority' => 'int',
				'status'   => 'int',
			],
			[
				Utils::$context['lp_category']['slug'],
				Utils::$context['lp_category']['icon'],
				$this->getPriority(),
				Utils::$context['lp_category']['status'],
			],
			['category_id'],
			1
		);

		if (empty($item)) {
			Db::$db->transaction('rollback');
			return 0;
		}

		$this->saveTranslations($item);

		Db::$db->transaction();

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('
			UPDATE {db_prefix}lp_categories
			SET slug = {string:slug}, icon = {string:icon}, priority = {int:priority}, status = {int:status}
			WHERE category_id = {int:category_id}',
			[
				'slug'        => Utils::$context['lp_category']['slug'],
				'icon'        => Utils::$context['lp_category']['icon'],
				'priority'    => Utils::$context['lp_category']['priority'],
				'status'      => Utils::$context['lp_category']['status'],
				'category_id' => $item,
			]
		);

		$this->saveTranslations($item, 'replace');

		Db::$db->transaction();
	}
}
