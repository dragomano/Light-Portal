<?php

/**
 * BlockImport.php
 *
 * @package TinyPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.12.21
 */

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Impex\AbstractOtherBlockImport;
use Bugo\LightPortal\Helper;

class BlockImport extends AbstractOtherBlockImport
{
	private array $supported_types = [5, 10, 11];

	public function main()
	{
		global $context, $txt, $scripturl;

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_tiny_portal']['label_name'];
		$context['page_area_title'] = $txt['lp_blocks_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_blocks;sa=import_from_tp';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_tiny_portal']['block_import_desc']
		);

		$this->run();

		$listOptions = array(
			'id' => 'lp_blocks',
			'items_per_page' => 50,
			'title' => $txt['lp_blocks_import'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'id',
			'get_items' => array(
				'function' => array($this, 'getAll')
			),
			'get_count' => array(
				'function' => array($this, 'getTotalCount')
			),
			'columns' => array(
				'id' => array(
					'header' => array(
						'value' => '#',
						'style' => 'width: 5%'
					),
					'data' => array(
						'db'    => 'id',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'id',
						'reverse' => 'id DESC'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'db'    => 'title',
						'class' => 'word_break'
					),
					'sort' => array(
						'default' => 'title DESC',
						'reverse' => 'title'
					)
				),
				'type' => array(
					'header' => array(
						'value' => $txt['lp_block_type']
					),
					'data' => array(
						'db'    => 'type',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'type DESC',
						'reverse' => 'type'
					)
				),
				'placement' => array(
					'header' => array(
						'value' => $txt['lp_block_placement']
					),
					'data' => array(
						'db'    => 'placement',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'bar DESC',
						'reverse' => 'bar'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					),
					'data' => array(
						'function' => fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="blocks[]" checked>',
						'class' => 'centertext'
					)
				)
			),
			'form' => array(
				'href' => $context['canonical_url']
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="import_selection" value="' . $txt['lp_tiny_portal']['button_run'] . '" class="button">
						<input type="submit" name="import_all" value="' . $txt['lp_tiny_portal']['button_all'] . '" class="button">'
				)
			)
		);

		Helper::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'lp_blocks';
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id'): array
	{
		global $smcFunc, $db_prefix, $txt, $context;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'tp_blocks')))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT id, type, title, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array(
				'types' => $this->supported_types,
				'sort'  => $sort,
				'start' => $start,
				'limit' => $items_per_page
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['id']] = array(
				'id'        => $row['id'],
				'type'      => $txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => $context['lp_block_placements'][$this->getPlacement($row['bar'])]
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(): int
	{
		global $smcFunc, $db_prefix;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'tp_blocks')))
			return 0;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})',
			array(
				'types' => $this->supported_types
			)
		);

		[$num_blocks] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $num_blocks;
	}

	protected function getItems(array $blocks): array
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT id, type, title, body, access, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})' . (empty($blocks) ? '' : '
				AND id IN ({array_int:blocks})'),
			array(
				'types'  => $this->supported_types,
				'blocks' => $blocks
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$permissions = explode(',', $row['access']);

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

			$items[$row['id']] = array(
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['body'],
				'placement'     => $this->getPlacement($row['bar']),
				'permissions'   => $perm,
				'status'        => 0,
				'title_class'   => array_key_first($context['lp_all_title_classes']),
				'content_class' => array_key_first($context['lp_all_content_classes'])
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	private function getType(int $type): string
	{
		switch ($type) {
			case 5:
				return 'bbc';
			case 10:
				return 'php';
			case 11:
			default:
				return 'html';
		}
	}

	private function getPlacement(int $bar): string
	{
		switch ($bar) {
			case 1:
				return 'left';
			case 2:
				return 'right';
			case 5:
				return 'footer';
			case 6:
				return 'header';
			case 7:
				return 'bottom';
			case 3:
			case 4:
			default:
				return 'top';
		}
	}
}
