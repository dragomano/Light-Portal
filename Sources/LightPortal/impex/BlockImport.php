<?php

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\Helpers;

/**
 * BlockImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BlockImport extends Import
{
	/**
	 * The page of import blocks
	 *
	 * Страница импорта блоков
	 *
	 * @return void
	 */
	public static function main()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageImport');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_import'];
		$context['page_area_title'] = $txt['lp_blocks_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_blocks;sa=import';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_import_tab_description']
		);

		$context['sub_template'] = 'manage_import';

		self::run();
	}

	/**
	 * Import from an XML file
	 *
	 * Импорт из XML-файла
	 *
	 * @return void
	 */
	protected static function run()
	{
		global $smcFunc;

		if (empty($_FILES['import_file']))
			return;

		$file = $_FILES['import_file'];

		if ($file['type'] !== 'text/xml')
			return;

		$xml = simplexml_load_file($file['tmp_name']);

		if ($xml === false)
			return;

		if (!isset($xml->blocks->item[0]['block_id']))
			fatal_lang_error('lp_wrong_import_file', false);

		$items = $titles = $params = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'block_id'      => $block_id = intval($item['block_id']),
					'icon'          => $item->icon,
					'icon_type'     => $item->icon_type,
					'type'          => $item->type,
					'content'       => $item->content,
					'placement'     => $item->placement,
					'priority'      => intval($item['priority']),
					'permissions'   => intval($item['permissions']),
					'status'        => intval($item['status']),
					'areas'         => $item->areas,
					'title_class'   => $item->title_class,
					'title_style'   => $item->title_style,
					'content_class' => $item->content_class,
					'content_style' => $item->content_style
				];

				if (!empty($item->titles)) {
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

				if (!empty($item->params)) {
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

		if (!empty($items)) {
			$result = $smcFunc['db_insert']('replace',
				'{db_prefix}lp_blocks',
				array(
					'block_id'      => 'int',
					'icon'          => 'string-60',
					'icon_type'     => 'string-10',
					'type'          => 'string',
					'content'       => 'string-' . MAX_MSG_LENGTH,
					'placement'     => 'string-10',
					'priority'      => 'int',
					'permissions'   => 'int',
					'status'        => 'int',
					'areas'         => 'string',
					'title_class'   => 'string',
					'title_style'   => 'string',
					'content_class' => 'string',
					'content_style' => 'string'
				),
				$items,
				array('block_id'),
				2
			);

			$smcFunc['lp_num_queries']++;
		}

		if (!empty($titles) && !empty($result)) {
			$result = $smcFunc['db_insert']('replace',
				'{db_prefix}lp_titles',
				array(
					'item_id' => 'int',
					'type'    => 'string',
					'lang'    => 'string',
					'title'   => 'string'
				),
				$titles,
				array('item_id', 'type', 'lang'),
				2
			);

			$smcFunc['lp_num_queries']++;
		}

		if (!empty($params) && !empty($result)) {
			$result = $smcFunc['db_insert']('replace',
				'{db_prefix}lp_params',
				array(
					'item_id' => 'int',
					'type'    => 'string',
					'name'    => 'string',
					'value'   => 'string'
				),
				$params,
				array('item_id', 'type', 'name'),
				2
			);

			$smcFunc['lp_num_queries']++;
		}

		if (empty($result))
			fatal_lang_error('lp_import_failed', false);

		Helpers::cache()->flush();
	}
}
