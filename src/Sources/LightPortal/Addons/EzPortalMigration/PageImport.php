<?php

/**
 * PageImport.php
 *
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 20.02.24
 */

namespace Bugo\LightPortal\Addons\EzPortalMigration;

use Bugo\Compat\{Config, Database as Db, Lang, User, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomPageImport;
use Bugo\LightPortal\Utils\{DateTime, ItemList};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractCustomPageImport
{
	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_ez_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['canonical_url']   = Config::$scripturl . '?action=admin;area=lp_pages;sa=import_from_ez';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_ez_portal_migration']['desc'],
		];

		$this->run();

		$listOptions = [
			'id' => 'ez_pages',
			'items_per_page' => 50,
			'title' => Lang::$txt['lp_pages_import'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'id',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => [$this, 'getTotalCount']
			],
			'columns' => [
				'id' => [
					'header' => [
						'value' => '#',
						'style' => 'width: 5%'
					],
					'data' => [
						'db'    => 'id',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'id_page',
						'reverse' => 'id_page DESC'
					]
				],
				'alias' => [
					'header' => [
						'value' => Lang::$txt['lp_page_alias']
					],
					'data' => [
						'db'    => 'alias',
						'class' => 'centertext word_break'
					]
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title']
					],
					'data' => [
						'db'    => 'title',
						'class' => 'word_break'
					],
					'sort' => [
						'default' => 'title DESC',
						'reverse' => 'title'
					]
				],
				'actions' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					],
					'data' => [
						'function' => static fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="pages[]" checked>',
						'class' => 'centertext'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['canonical_url']
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="import_selection" value="' . Lang::$txt['lp_import_selection'] . '" class="button">
						<input type="submit" name="import_all" value="' . Lang::$txt['lp_import_all'] . '" class="button">'
				]
			]
		];

		new ItemList($listOptions);
	}

	/**
	 * @throws IntlException
	 */
	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_page'): array
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'ezp_page')))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT id_page, date, title, views
			FROM {db_prefix}ezp_page
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id_page']] = [
				'id'         => $row['id_page'],
				'alias'      => Utils::$smcFunc['strtolower'](explode(' ', $row['title'])[0]) . $row['id_page'],
				'type'       => 'html',
				'status'     => 1,
				'num_views'  => $row['views'],
				'author_id'  => User::$info['id'],
				'created_at' => DateTime::relative($row['date']),
				'title'      => $row['title'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(): int
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'ezp_page')))
			return 0;

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}ezp_page',
			[]
		);

		[$count] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $count;
	}

	protected function getItems(array $pages): array
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT id_page, date, title, content, views, permissions
			FROM {db_prefix}ezp_page' . (empty($pages) ? '' : '
			WHERE id_page IN ({array_int:pages})'),
			[
				'pages' => $pages,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$permissions = explode(',', $row['permissions']);

			$perm = 0;
			if (count($permissions) == 1 && $permissions[0] == -1) {
				$perm = 1;
			} elseif (count($permissions) == 1 && $permissions[0] == 0) {
				$perm = 2;
			} elseif (in_array(-1, $permissions)) {
				$perm = 3;
			} elseif (in_array(0, $permissions)) {
				$perm = 3;
			}

			$items[$row['id_page']] = [
				'page_id'      => $row['id_page'],
				'author_id'    => User::$info['id'],
				'alias'        => 'page_' . $row['id_page'],
				'description'  => '',
				'content'      => $row['content'],
				'type'         => 'html',
				'permissions'  => $perm,
				'status'       => 1,
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'subject'      => $row['title'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
