<?php

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\{Helpers, ManageBlocks};

/**
 * BlockExport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BlockExport extends AbstractExport
{
	/**
	 * The page of export blocks
	 *
	 * Страница экспорта блоков
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageImpex');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_export'];
		$context['page_area_title'] = $txt['lp_blocks_export'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_blocks;sa=export';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_export_description']
		);

		$this->run();

		$context['lp_current_blocks'] = (new ManageBlocks)->getAll();
		$context['lp_current_blocks'] = array_merge(array_flip(array_keys($context['lp_block_placements'])), $context['lp_current_blocks']);

		$context['sub_template'] = 'manage_export_blocks';
	}

	/**
	 * @return array
	 */
	protected function getData(): array
	{
		global $smcFunc;

		if (Helpers::post()->isEmpty('blocks') && Helpers::post()->has('export_all') === false)
			return [];

		$blocks = !empty(Helpers::post('blocks')) && Helpers::post()->has('export_all') === false ? Helpers::post('blocks') : null;

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.user_id, b.icon, b.type, b.note, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.title_style, b.content_class, b.content_style, pt.lang, pt.title, pp.name, pp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS pt ON (b.block_id = pt.item_id AND pt.type = {literal:block})
				LEFT JOIN {db_prefix}lp_params AS pp ON (b.block_id = pp.item_id AND pp.type = {literal:block})' . (!empty($blocks) ? '
			WHERE b.block_id IN ({array_int:blocks})' : ''),
			array(
				'blocks' => $blocks
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($items[$row['block_id']]))
				$items[$row['block_id']] = array(
					'block_id'      => $row['block_id'],
					'user_id'       => $row['user_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'note'          => $row['note'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'status'        => $row['status'],
					'areas'         => $row['areas'],
					'title_class'   => $row['title_class'],
					'title_style'   => $row['title_style'],
					'content_class' => $row['content_class'],
					'content_style' => $row['content_style']
				);

			if (!empty($row['lang']) && !empty($row['title']))
				$items[$row['block_id']]['titles'][$row['lang']] = $row['title'];

			if (!empty($row['name']) && !empty($row['value']))
				$items[$row['block_id']]['params'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * Get filename with XML data
	 *
	 * Получаем имя файла с XML-данными
	 *
	 * @return string
	 */
	protected function getXmlFile(): string
    {
		if (empty($items = $this->getData()))
			return '';

		$xml = new \DomDocument('1.0', 'utf-8');
		$root = $xml->appendChild($xml->createElement('light_portal'));

		$xml->formatOutput = true;

		$xmlElements = $root->appendChild($xml->createElement('blocks'));
		foreach ($items as $item) {
			$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
			foreach ($item as $key => $val) {
				$xmlName = $xmlElement->appendChild(in_array($key, ['block_id', 'user_id', 'priority', 'permissions', 'status']) ? $xml->createAttribute($key) : $xml->createElement($key));

				if (in_array($key, ['titles', 'params'])) {
					foreach ($val as $k => $v) {
						$xmlTitle = $xmlName->appendChild($xml->createElement($k));
						$xmlTitle->appendChild($xml->createTextNode($v));
					}
				} elseif ($key == 'content') {
					$xmlName->appendChild($xml->createCDATASection($val));
				} else {
					$xmlName->appendChild($xml->createTextNode($val));
				}
			}
		}

		$file = sys_get_temp_dir() . '/lp_blocks_backup.xml';
		$xml->save($file);

		return $file;
	}
}
