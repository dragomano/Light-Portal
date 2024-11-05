<?php

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 10.10.24
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Compat\{BBCodeParser, Config, Db, Lang, User, Utils};
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

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['page_import_desc'],
		];

		$this->run();

		$listOptions = [
			'id' => 'tp_pages',
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
						'default' => 'id',
						'reverse' => 'id DESC'
					]
				],
				'slug' => [
					'header' => [
						'value' => Lang::$txt['lp_page_slug']
					],
					'data' => [
						'db'    => 'slug',
						'class' => 'centertext word_break'
					],
					'sort' => [
						'default' => 'shortname DESC',
						'reverse' => 'shortname'
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
						'default' => 'subject DESC',
						'reverse' => 'subject'
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
	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'tp_articles')))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT id, date, subject, author_id, off, views, shortname, type
			FROM {db_prefix}tp_articles
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
			$items[$row['id']] = [
				'id'         => $row['id'],
				'slug'       => $row['shortname'],
				'type'       => $row['type'],
				'status'     => (int) empty($row['off']),
				'num_views'  => $row['views'],
				'author_id'  => $row['author_id'],
				'created_at' => DateTime::relative($row['date']),
				'title'      => $row['subject'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix. 'tp_articles')))
			return 0;

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}tp_articles',
			[]
		);

		[$count] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				a.id, a.date, a.body, a.intro, a.subject, a.author_id, a.off, a.options, a.comments, a.views,
				a.shortname, a.type, a.pub_start, a.pub_end, v.value3
			FROM {db_prefix}tp_articles AS a
				LEFT JOIN {db_prefix}tp_variables AS v ON (
					a.category = v.id AND v.type = {string:type}
				)' . (empty($ids) ? '' : '
			WHERE a.id IN ({array_int:pages})'),
			[
				'type'  => 'category',
				'pages' => $ids,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {


			$items[$row['id']] = [
				'page_id'      => $row['id'],
				'author_id'    => $row['author_id'],
				'slug'         => $row['shortname'] ?: ('page_' . $row['id']),
				'description'  => strip_tags((string) BBCodeParser::load()->parse($row['intro'])),
				'content'      => $row['body'],
				'type'         => $row['type'],
				'permissions'  => $this->getPagePermission($row),
				'status'       => (int) empty($row['off']),
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'subject'      => $row['subject'],
				'options'      => explode(',', (string) $row['options']),
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}

	private function getPagePermission(array $row): int
	{
		$permissions = explode(',', (string) $row['value3']);

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
