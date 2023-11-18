<?php declare(strict_types=1);

/**
 * BlockImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Impex;

if (! defined('SMF'))
	die('No direct access...');

final class BlockImport extends AbstractImport
{
	public function main(): void
	{
		$this->loadTemplate('LightPortal/ManageImpex', 'manage_import');

		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_blocks_import'];
		$this->context['page_area_title'] = $this->txt['lp_blocks_import'];
		$this->context['page_area_info']  = $this->txt['lp_blocks_import_info'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_blocks;sa=import';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_blocks_import_description']
		];

		$this->context['lp_file_type'] = 'text/xml';

		$this->run();
	}

	protected function run(): void
	{
		if (empty($xml = $this->getFile()))
			return;

		if (! isset($xml->blocks->item[0]['block_id']))
			$this->fatalLangError('lp_wrong_import_file');

		$items = $titles = $params = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'block_id'      => $block_id = intval($item['block_id']),
					'icon'          => $item->icon,
					'type'          => str_replace('md', 'markdown', (string) $item->type),
					'note'          => $item->note,
					'content'       => $item->content,
					'placement'     => $item->placement,
					'priority'      => intval($item['priority']),
					'permissions'   => $item['user_id'] > 0 ? 4 : intval($item['permissions']),
					'status'        => intval($item['status']),
					'areas'         => $item->areas,
					'title_class'   => str_contains((string) $item->title_class, 'div.') ? 'cat_bar' : $item->title_class,
					'content_class' => str_contains((string) $item->content_class, 'div.') ? 'roundframe' : $item->content_class,
				];

				if ($item->titles) {
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

				if ($item->params) {
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

		$results = [];

		if ($items) {
			$this->context['import_successful'] = count($items);

			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_blocks',
					[
						'block_id'      => 'int',
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
						'content_class' => 'string',
					],
					$items[$i],
					['block_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		$this->replaceTitles($titles, $results);

		$this->replaceParams($params, $results);

		$this->finish($results);
	}
}
