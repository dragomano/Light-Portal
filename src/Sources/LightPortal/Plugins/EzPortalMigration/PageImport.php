<?php

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 10.10.24
 */

namespace Bugo\LightPortal\Plugins\EzPortalMigration;

use Bugo\Compat\{Config, Db, Lang, User, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomPageImport;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Utils\{DateTime, ItemList};
use IntlException;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractCustomPageImport
{
	public function main(): void
	{
		User::mustHavePermission('admin_forum');

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_ez_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=import_from_ez';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_ez_portal_migration']['page_import_desc'],
		];

		$this->run();

		$listOptions = [
			'id' => 'ez_pages',
			'items_per_page' => 50,
			'title' => Lang::$txt['lp_pages_import'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
			'default_sort_col' => 'id',
			'get_items' => [
				'function' => $this->getAll(...)
			],
			'get_count' => [
				'function' => $this->getTotalCount(...)
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
				'slug' => [
					'header' => [
						'value' => Lang::$txt['lp_page_slug']
					],
					'data' => [
						'db'    => 'slug',
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
				'href' => Utils::$context['form_action']
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
				'slug'       => Utils::$smcFunc['strtolower'](explode(' ', (string) $row['title'])[0]) . $row['id_page'],
				'type'       => 'html',
				'status'     => 1,
				'num_views'  => $row['views'],
				'author_id'  => User::$info['id'],
				'created_at' => DateTime::relative($row['date']),
				'title'      => $row['title'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

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

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT id_page, date, title, content, views, permissions
			FROM {db_prefix}ezp_page' . (empty($ids) ? '' : '
			WHERE id_page IN ({array_int:pages})'),
			[
				'pages' => $ids,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id_page']] = [
				'page_id'      => $row['id_page'],
				'author_id'    => User::$info['id'],
				'slug'         => 'page_' . $row['id_page'],
				'description'  => '',
				'content'      => $row['content'],
				'type'         => 'html',
				'permissions'  => $this->getPagePermission($row),
				'status'       => 1,
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'subject'      => $row['title'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}

	private function getPagePermission(array $row): int
	{
		$permissions = explode(',', (string) $row['permissions']);

		$perm = Permission::ADMIN->value;
		if (count($permissions) == 1 && $permissions[0] == -1) {
			$perm = Permission::GUEST->value;
		} elseif (count($permissions) == 1 && $permissions[0] == 0) {
			$perm = Permission::MEMBER->value;
		} elseif (in_array(-1, $permissions)) {
			$perm = Permission::ALL->value;
		} elseif (in_array(0, $permissions)) {
			$perm = Permission::ALL->value;
		}

		return $perm;
	}
}
