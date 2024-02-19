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
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\{BBCodeParser, Config};
use Bugo\Compat\{Database as Db, ErrorHandler};
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

		Utils::$context['lp_category'] = $this->request('id');

		$categories = $this->getEntityData('category');
		if (array_key_exists(Utils::$context['lp_category'], $categories) === false) {
			Utils::$context['error_link'] = LP_BASE_URL . ';sa=categories';
			Lang::$txt['back'] = Lang::$txt['lp_all_categories'];
			ErrorHandler::fatalLang('lp_category_not_found', status: 404);
		}

		$category = [];
		if (empty(Utils::$context['lp_category'])) {
			Utils::$context['page_title'] = Lang::$txt['lp_all_pages_without_category'];
		} else {
			$category = $categories[Utils::$context['lp_category']];
			Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_pages_with_category'], $category['name']);
		}

		Utils::$context['description'] = $category['desc'] ?? '';

		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=categories;id=' . Utils::$context['lp_category'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Lang::$txt['lp_all_categories'],
			'url'  => LP_BASE_URL . ';sa=categories'
		];

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title']
		];

		if (! empty(Config::$modSettings['lp_show_items_as_articles']))
			$page->showAsCards($this);

		$listOptions = $page->getList();
		$listOptions['id'] = 'lp_categories';
		$listOptions['get_items'] = [
			'function' => [$this, 'getPages']
		];
		$listOptions['get_count'] = [
			'function' => [$this, 'getTotalCount']
		];

		if (isset($category['desc'])) {
			$listOptions['additional_rows'] = [
				[
					'position' => 'top_of_list',
					'value'    => $category['desc'],
					'class'    => 'information'
				]
			];
		}

		new ItemList($listOptions);

		Utils::obExit();
	}

	/**
	 * @throws IntlException
	 */
	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.content, p.description, p.type,
			    p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, \'\') AS author_name, t.title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
			WHERE p.category_id = {int:id}
				AND p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'lang'         => User::$info['language'],
				'id'           => Utils::$context['lp_category'],
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
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
				'id'           => Utils::$context['lp_category'],
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions()
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
			'name' => Utils::$context['page_title']
		];

		$listOptions = [
			'id' => 'categories',
			'items_per_page' => Config::$modSettings['defaultMaxListItems'] ?: 50,
			'title' => Utils::$context['page_title'],
			'no_items_label' => Lang::$txt['lp_no_categories'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'name',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => fn() => count($this->getAll())
			],
			'columns' => [
				'name' => [
					'header' => [
						'value' => Lang::$txt['lp_category']
					],
					'data' => [
						'function' => fn($entry) => '<a href="' . $entry['link'] . '">' . $entry['name'] . '</a>' .
							(empty($entry['desc']) ? '' : '<p class="smalltext">' . $entry['desc'] . '</p>')
					],
					'sort' => [
						'default' => 'c.name DESC',
						'reverse' => 'c.name'
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

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'c.name'): array
	{
		$result = Db::$db->query('', '
			SELECT COALESCE(c.category_id, 0) AS category_id, c.name, c.description, COUNT(p.page_id) AS frequency
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_categories AS c ON (p.category_id = c.category_id)
			WHERE p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY c.category_id, c.name, c.description
			ORDER BY {raw:sort}' . ($items_per_page ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if ($row['description'] && str_contains($row['description'], ']')) {
				$row['description'] = BBCodeParser::load()->parse($row['description']);
			}

			$items[$row['category_id']] = [
				'name'      => $row['name'] ?: Lang::$txt['lp_no_category'],
				'desc'      => $row['description'] ?? '',
				'link'      => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
				'num_pages' => $row['frequency']
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
