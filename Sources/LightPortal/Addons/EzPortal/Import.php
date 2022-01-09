<?php

/**
 * Import.php
 *
 * @package EzPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\EzPortal;

use Bugo\LightPortal\Impex\AbstractOtherPageImport;

if (! defined('LP_NAME'))
	die('No direct access...');

class Import extends AbstractOtherPageImport
{
	public function main()
	{
		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_ez_portal']['label_name'];
		$this->context['page_area_title'] = $this->txt['lp_pages_import'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_pages;sa=import_from_ez';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_ez_portal']['desc']
		];

		$this->run();

		$listOptions = [
			'id' => 'lp_pages',
			'items_per_page' => 50,
			'title' => $this->txt['lp_pages_import'],
			'no_items_label' => $this->txt['lp_no_items'],
			'base_href' => $this->context['canonical_url'],
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
						'value' => $this->txt['lp_page_alias']
					],
					'data' => [
						'db'    => 'alias',
						'class' => 'centertext word_break'
					]
				],
				'title' => [
					'header' => [
						'value' => $this->txt['lp_title']
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
						'function' => fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="pages[]" checked>',
						'class' => 'centertext'
					]
				]
			],
			'form' => [
				'href' => $this->context['canonical_url']
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="import_selection" value="' . $this->txt['lp_ez_portal']['button_run'] . '" class="button">
						<input type="submit" name="import_all" value="' . $this->txt['lp_ez_portal']['button_all'] . '" class="button">'
				]
			]
		];

		$this->require('Subs-List');
		createList($listOptions);

		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = 'lp_pages';
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id_page'): array
	{
		db_extend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'ezp_page')))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT id_page, date, title, views
			FROM {db_prefix}ezp_page
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'sort'  => $sort,
				'start' => $start,
				'limit' => $items_per_page
			]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['id_page']] = [
				'id'         => $row['id_page'],
				'alias'      => $this->smcFunc['strtolower'](explode(' ', $row['title'])[0]) . $row['id_page'],
				'type'       => 'html',
				'status'     => 1,
				'num_views'  => $row['views'],
				'author_id'  => $this->user_info['id'],
				'created_at' => $this->getFriendlyTime($row['date']),
				'title'      => $row['title']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(): int
	{
		db_extend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'ezp_page')))
			return 0;

		$request = $this->smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}ezp_page',
			[]
		);

		[$num_pages] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_pages;
	}

	protected function getItems(array $pages): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT id_page, date, title, content, views, permissions
			FROM {db_prefix}ezp_page' . (empty($pages) ? '' : '
			WHERE id_page IN ({array_int:pages})'),
			[
				'pages' => $pages
			]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
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
				'author_id'    => $this->user_info['id'],
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
				'subject'      => $row['title']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
