<?php

/**
 * Import.php
 *
 * @package EhPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.12.22
 */

namespace Bugo\LightPortal\Addons\EhPortal;

use Bugo\LightPortal\Impex\AbstractOtherPageImport;

if (! defined('LP_NAME'))
	die('No direct access...');

class Import extends AbstractOtherPageImport
{
	public function main()
	{
		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_eh_portal']['label_name'];
		$this->context['page_area_title'] = $this->txt['lp_pages_import'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_pages;sa=import_from_ep';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_eh_portal']['desc']
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
					],
					'sort' => [
						'default' => 'namespace DESC',
						'reverse' => 'namespace'
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
						<input type="submit" name="import_selection" value="' . $this->txt['lp_import_selection'] . '" class="button">
						<input type="submit" name="import_all" value="' . $this->txt['lp_import_all'] . '" class="button">'
				]
			]
		];

		$this->createList($listOptions);
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id_page'): array
	{
		$this->dbExtend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'sp_pages')))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT id_page, namespace, title, body, type, permission_set, groups_allowed, views, status
			FROM {db_prefix}sp_pages
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
				'alias'      => $row['namespace'],
				'type'       => $row['type'],
				'status'     => $row['status'],
				'num_views'  => $row['views'],
				'author_id'  => $this->user_info['id'],
				'created_at' => $this->getFriendlyTime(time()),
				'title'      => $row['title']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(): int
	{
		$this->dbExtend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'sp_pages')))
			return 0;

		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}sp_pages',
			[]
		);

		[$num_pages] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_pages;
	}

	protected function getItems(array $pages): array
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT id_page, namespace, title, body, type, permission_set, groups_allowed, views, status
			FROM {db_prefix}sp_pages' . (empty($pages) ? '' : '
			WHERE id_page IN ({array_int:pages})'),
			[
				'pages' => $pages
			]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$perm = $row['permission_set'];

			if (empty($row['permission_set'])) {
				$groups = $row['groups_allowed'];

				if ($groups == -1) {
					$perm = 1;
				} elseif ($groups == 0) {
					$perm = 2;
				} elseif ($groups == 1) {
					$perm = 0;
				} else {
					$perm = 3;
				}
			}

			$items[$row['id_page']] = [
				'page_id'      => $row['id_page'],
				'author_id'    => $this->user_info['id'],
				'alias'        => $row['namespace'] ?: ('page_' . $row['id_page']),
				'description'  => '',
				'content'      => $row['body'],
				'type'         => $row['type'],
				'permissions'  => $perm,
				'status'       => $row['status'],
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => time(),
				'updated_at'   => 0,
				'title'        => $row['title']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
