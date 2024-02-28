<?php declare(strict_types=1);

/**
 * Category.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\{Config, Db, ErrorHandler};
use Bugo\Compat\{Lang, User, Utils};
use Bugo\LightPortal\Utils\ItemList;
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class Category extends AbstractPageList
{
	public function show(PageInterface $page): void
	{
		if ($this->request()->hasNot('id'))
			$this->showAll();

		$category = [
			'id' => (int) $this->request('id', 0)
		];

		$categories = $this->getEntityData('category');
		if (array_key_exists($category['id'], $categories) === false) {
			Utils::$context['error_link'] = LP_BASE_URL . ';sa=categories';
			Lang::$txt['back'] = Lang::$txt['lp_all_categories'];
			ErrorHandler::fatalLang('lp_category_not_found', status: 404);
		}

		if ($category['id'] === 0) {
			Utils::$context['page_title'] = Lang::$txt['lp_all_pages_without_category'];
		} else {
			$category = $categories[$category['id']];
			Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_pages_with_category'], $category['title']);
		}

		Utils::$context['current_category'] = $category['id'];

		Utils::$context['description'] = $category['description'] ?? '';

		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=categories;id=' . $category['id'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Lang::$txt['lp_all_categories'],
			'url'  => LP_BASE_URL . ';sa=categories',
		];

		Utils::$context['linktree'][] = [
			'name' => $category['title'],
		];

		$page->showAsCards($this);

		$listOptions = $page->getList();
		$listOptions['id'] = 'lp_categories';
		$listOptions['get_items'] = [
			'function' => [$this, 'getPages']
		];
		$listOptions['get_count'] = [
			'function' => [$this, 'getTotalCount']
		];

		if (isset($category['description'])) {
			$listOptions['additional_rows'] = [
				[
					'position' => 'top_of_list',
					'value'    => $category['description'],
					'class'    => 'information',
				]
			];
		}

		new ItemList($listOptions);

		Utils::obExit();
	}

	/**
	 * @throws IntlException
	 */
	public function getPages(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.content, p.description, p.type,
				p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, \'\') AS author_name, COALESCE(t.title, tf.title) AS title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE p.category_id = {int:id}
				AND p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'id'            => Utils::$context['current_category'],
				'statuses'      => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time'  => time(),
				'permissions'   => $this->getPermissions(),
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$rows = Db::$db->fetch_all($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $this->getPreparedResults($rows);
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE category_id = {string:id}
				AND status IN ({array_int:statuses})
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})',
			[
				'id'           => Utils::$context['current_category'],
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return (int) $count;
	}

	public function showAll(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_all_categories'];
		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=categories';
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title'],
		];

		$listOptions = [
			'id' => 'categories',
			'items_per_page' => Config::$modSettings['defaultMaxListItems'] ?: 50,
			'title' => Utils::$context['page_title'],
			'no_items_label' => Lang::$txt['lp_no_categories'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'priority',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => fn() => count($this->getAll())
			],
			'columns' => [
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_category']
					],
					'data' => [
						'function' => static fn($entry) => $entry['icon'] . ' ' . '<a href="' . $entry['link'] . '">' . $entry['title'] . '</a>' .
							(empty($entry['description']) ? '' : '<p class="smalltext">' . $entry['description'] . '</p>')
					],
					'sort' => [
						'default' => 't.title DESC',
						'reverse' => 't.title'
					]
				],
				'priority' => [
					'header' => [
						'value' => Lang::$txt['lp_block_priority']
					],
					'data' => [
						'db'    => 'priority',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'c.priority',
						'reverse' => 'c.priority DESC'
					]
				],
				'num_pages' => [
					'header' => [
						'value' => Lang::$txt['lp_total_pages_column']
					],
					'data' => [
						'db'    => 'num_pages',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'frequency DESC',
						'reverse' => 'frequency'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['canonical_url']
			]
		];

		new ItemList($listOptions);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 't.title'): array
	{
		$result = Db::$db->query('', '
			SELECT
				COALESCE(c.category_id, 0) AS category_id, c.icon, c.description, c.priority,
				COUNT(p.page_id) AS frequency, COALESCE(t.title, tf.title) AS cat_title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS c ON (p.category_id = c.category_id)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					c.category_id = t.item_id AND t.type = {literal:category} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					c.category_id = tf.item_id AND tf.type = {literal:category} AND tf.lang = {string:fallback_lang}
				)
			WHERE (c.status = {int:status} OR p.category_id = 0)
				AND p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY c.category_id, c.icon, c.description, c.priority, t.title, tf.title
			ORDER BY {raw:sort}' . ($limit ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'status'        => self::STATUS_ACTIVE,
				'statuses'      => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time'  => time(),
				'permissions'   => $this->getPermissions(),
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['category_id']] = [
				'icon'        => $this->getIcon($row['icon']),
				'title'       => $row['cat_title'] ?: Lang::$txt['lp_no_category'],
				'description' => $row['description'] ?? '',
				'link'        => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
				'priority'    => (int) $row['priority'],
				'num_pages'   => (int) $row['frequency'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
