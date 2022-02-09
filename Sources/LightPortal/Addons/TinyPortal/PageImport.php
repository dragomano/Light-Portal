<?php

/**
 * PageImport.php
 *
 * @package TinyPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Impex\AbstractOtherPageImport;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageImport extends AbstractOtherPageImport
{
	public function main()
	{
		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_tiny_portal']['label_name'];
		$this->context['page_area_title'] = $this->txt['lp_pages_import'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_pages;sa=import_from_tp';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_tiny_portal']['page_import_desc']
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
						'default' => 'id',
						'reverse' => 'id DESC'
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
						'default' => 'shortname DESC',
						'reverse' => 'shortname'
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
				'href' => $this->context['canonical_url']
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="import_selection" value="' . $this->txt['lp_tiny_portal']['button_run'] . '" class="button">
						<input type="submit" name="import_all" value="' . $this->txt['lp_tiny_portal']['button_all'] . '" class="button">'
				]
			]
		];

		$this->require('Subs-List');
		createList($listOptions);

		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = 'lp_pages';
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id'): array
	{
		db_extend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'tp_articles')))
			return [];

		$request = $this->smcFunc['db_query']('', '
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
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['id']] = [
				'id'         => $row['id'],
				'alias'      => $row['shortname'],
				'type'       => $row['type'],
				'status'     => (int) empty($row['off']),
				'num_views'  => $row['views'],
				'author_id'  => $row['author_id'],
				'created_at' => $this->getFriendlyTime($row['date']),
				'title'      => $row['subject']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(): int
	{
		db_extend();

		if (empty($this->smcFunc['db_list_tables'](false, $this->db_prefix . 'tp_articles')))
			return 0;

		$request = $this->smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_articles',
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
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
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
				'description'  => strip_tags(parse_bbc($row['intro'])),
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

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}
}
