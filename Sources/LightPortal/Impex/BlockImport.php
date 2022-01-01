<?php

declare(strict_types = 1);

/**
 * BlockImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Impex;

if (! defined('SMF'))
	die('No direct access...');

final class BlockImport extends AbstractImport
{
	public function main()
	{
		loadTemplate('LightPortal/ManageImpex');

		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_blocks_import'];
		$this->context['page_area_title'] = $this->txt['lp_blocks_import'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_blocks;sa=import';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_blocks_import_description']
		];

		$this->context['sub_template'] = 'manage_import';

		$this->run();
	}

	protected function run()
	{
		if (empty($file = $this->file('import_file')->get()))
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$this->db_temp_cache = $this->db_cache;
		$this->db_cache = [];

		if ($file['type'] !== 'text/xml')
			return;

		$xml = simplexml_load_file($file['tmp_name']);

		if ($xml === false)
			return;

		if (! isset($xml->blocks->item[0]['block_id']))
			fatal_lang_error('lp_wrong_import_file', false);

		$items = $titles = $params = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'block_id'      => $block_id = intval($item['block_id']),
					'user_id'       => intval($item['user_id']),
					'icon'          => $item->icon,
					'type'          => str_replace('md', 'markdown', $item->type),
					'note'          => $item->note,
					'content'       => $item->content,
					'placement'     => $item->placement,
					'priority'      => intval($item['priority']),
					'permissions'   => intval($item['permissions']),
					'status'        => intval($item['status']),
					'areas'         => $item->areas,
					'title_class'   => strpos((string) $item->title_class, 'div.') !== false ? 'cat_bar' : $item->title_class,
					'title_style'   => $item->title_style,
					'content_class' => strpos((string) $item->content_class, 'div.') !== false ? 'roundframe' : $item->content_class,
					'content_style' => $item->content_style
				];

				if (! empty($item->titles)) {
					foreach ($item->titles as $title) {
						foreach ($title as $k => $v) {
							$titles[] = [
								'item_id' => $block_id,
								'type'    => 'block',
								'lang'    => $k,
								'title'   => $v
							];
						}
					}
				}

				if (! empty($item->params)) {
					foreach ($item->params as $param) {
						foreach ($param as $k => $v) {
							$params[] = [
								'item_id' => $block_id,
								'type'    => 'block',
								'name'    => $k,
								'value'   => $v
							];
						}
					}
				}
			}
		}

		$this->smcFunc['db_transaction']('begin');

		if (! empty($items)) {
			$this->context['import_successful'] = count($items);
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_blocks',
					[
						'block_id'      => 'int',
						'user_id'       => 'int',
						'icon'          => 'string',
						'type'          => 'string',
						'note'          => 'string',
						'content'       => 'string-65534',
						'placement'     => 'string-10',
						'priority'      => 'int',
						'permissions'   => 'int',
						'status'        => 'int',
						'areas'         => 'string',
						'title_class'   => 'string',
						'title_style'   => 'string',
						'content_class' => 'string',
						'content_style' => 'string'
					],
					$items[$i],
					['block_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if (! empty($titles) && ! empty($results)) {
			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_titles',
					[
						'item_id' => 'int',
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string'
					],
					$titles[$i],
					['item_id', 'type', 'lang'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if (! empty($params) && ! empty($results)) {
			$params = array_chunk($params, 100);
			$count  = sizeof($params);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_params',
					[
						'item_id' => 'int',
						'type'    => 'string',
						'name'    => 'string',
						'value'   => 'string'
					],
					$params[$i],
					['item_id', 'type', 'name'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if (empty($results)) {
			$this->smcFunc['db_transaction']('rollback');
			fatal_lang_error('lp_import_failed', false);
		}

		$this->smcFunc['db_transaction']('commit');

		$this->context['import_successful'] = sprintf($this->txt['lp_import_success'], __('lp_blocks_set', ['blocks' => $this->context['import_successful']]));

		// Restore the cache
		$this->db_cache = $this->db_temp_cache;

		$this->cache()->flush();
	}
}
