<?php

/**
 * PageImport.php
 *
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 10.02.24
 */

namespace Bugo\LightPortal\Addons\TinyPortalMigration;

use Bugo\Compat\{BBCodeParser, Config, Database as Db, Lang, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomPageImport;
use Bugo\LightPortal\Utils\{DateTime, ItemList};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractCustomPageImport
{
	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_import'];
		Utils::$context['canonical_url']   = Config::$scripturl . '?action=admin;area=lp_pages;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['page_import_desc']
		];

		$this->run();

		$listOptions = [
			'id' => 'tp_pages',
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
						'default' => 'id',
						'reverse' => 'id DESC'
					]
				],
				'alias' => [
					'header' => [
						'value' => Lang::$txt['lp_page_alias']
					],
					'data' => [
						'db'    => 'alias',
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
						'function' => fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="pages[]" checked>',
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
	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id'): array
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
				'limit' => $items_per_page
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id']] = [
				'id'         => $row['id'],
				'alias'      => $row['shortname'],
				'type'       => $row['type'],
				'status'     => (int) empty($row['off']),
				'num_views'  => $row['views'],
				'author_id'  => $row['author_id'],
				'created_at' => DateTime::relative($row['date']),
				'title'      => $row['subject']
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

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

		[$num_pages] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $num_pages;
	}

	protected function getItems(array $pages): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT a.id, a.date, a.body, a.intro, a.subject, a.author_id, a.off, a.options, a.comments, a.views, a.shortname, a.type, a.pub_start, a.pub_end, v.value3
			FROM {db_prefix}tp_articles AS a
				LEFT JOIN {db_prefix}tp_variables AS v ON (a.category = v.id AND v.type = {string:type})' . (empty($pages) ? '' : '
			WHERE a.id IN ({array_int:pages})'),
			[
				'type'  => 'category',
				'pages' => $pages
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$permissions = explode(',', $row['value3']);

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

			$items[$row['id']] = [
				'page_id'      => $row['id'],
				'author_id'    => $row['author_id'],
				'alias'        => $row['shortname'] ?: ('page_' . $row['id']),
				'description'  => strip_tags(BBCodeParser::load()->parse($row['intro'])),
				'content'      => $row['body'],
				'type'         => $row['type'],
				'permissions'  => $perm,
				'status'       => (int) empty($row['off']),
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'subject'      => $row['subject'],
				'options'      => explode(',', $row['options'])
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}
