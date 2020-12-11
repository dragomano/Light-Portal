<?php

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\Helpers;
use Bugo\LightPortal\ManagePages;

/**
 * PageExport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PageExport extends AbstractExport
{
	/**
	 * Prepare to export
	 *
	 * Экспорт страниц
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_pages_export'];
		$context['page_area_title'] = $txt['lp_pages_export'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=export';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_export_tab_description']
		);

		$this->run();

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => ($pages = new ManagePages)->num_pages,
			'title' => $txt['lp_pages_export'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $scripturl . '?action=admin;area=lp_pages;sa=export',
			'default_sort_col' => 'id',
			'get_items' => array(
				'function' => array($pages, 'getAll')
			),
			'get_count' => array(
				'function' => array($pages, 'getTotalQuantity')
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
						'default' => 'p.page_id',
						'reverse' => 'p.page_id DESC'
					)
				),
				'alias' => array(
					'header' => array(
						'value' => $txt['lp_page_alias']
					),
					'data' => array(
						'db'    => 'alias',
						'class' => 'centertext word_break'
					),
					'sort' => array(
						'default' => 'p.alias DESC',
						'reverse' => 'p.alias'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							$title = Helpers::getTitle($entry);

							return '<a class="bbc_link' . (
								$entry['is_front']
									? ' new_posts" href="' . $scripturl
									: '" href="' . $scripturl . '?page=' . $entry['alias']
								) . '">' . $title . '</a>';
						},
						'class' => 'word_break'
					),
					'sort' => array(
						'default' => 't.title DESC',
						'reverse' => 't.title'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<input type="checkbox" value="' . $entry['id'] . '" name="pages[]" checked>';
						},
						'class' => 'centertext'
					)
				)
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=lp_pages;sa=export'
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '
						<input type="submit" name="export_selection" value="' . $txt['lp_export_run'] . '" class="button">
						<input type="submit" name="export_all" value="' . $txt['lp_export_all'] . '" class="button">',
					'class' => 'floatright'
				)
			)
		);

		Helpers::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'pages';
	}

	/**
	 * Creating data in XML format
	 *
	 * Формируем данные в XML-формате
	 *
	 * @return mixed
	 */
	protected function getData()
	{
		global $smcFunc;

		if (Helpers::post()->isEmpty('pages') && Helpers::post()->has('export_all') === false)
			return false;

		$pages = !empty(Helpers::post('pages')) && Helpers::post()->has('export_all') === false ? Helpers::post('pages') : null;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions, p.status, p.num_views, p.num_comments, p.created_at, p.updated_at,
				pt.lang, pt.title, pp.name, pp.value, t.value AS keyword, com.id, com.parent_id, com.author_id AS com_author_id, com.message, com.created_at AS com_created_at
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {string:type})
				LEFT JOIN {db_prefix}lp_tags AS t ON (p.page_id = t.page_id)
				LEFT JOIN {db_prefix}lp_comments AS com ON (p.page_id = com.page_id)' . (!empty($pages) ? '
			WHERE p.page_id IN ({array_int:pages})' : ''),
			array(
				'type'  => 'page',
				'pages' => $pages
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($items[$row['page_id']]))
				$items[$row['page_id']] = array(
					'page_id'      => $row['page_id'],
					'author_id'    => $row['author_id'],
					'alias'        => $row['alias'],
					'description'  => trim($row['description']),
					'content'      => $row['content'],
					'type'         => $row['type'],
					'permissions'  => $row['permissions'],
					'status'       => $row['status'],
					'num_views'    => $row['num_views'],
					'num_comments' => $row['num_comments'],
					'created_at'   => $row['created_at'],
					'updated_at'   => $row['updated_at']
				);

			if (!empty($row['lang']))
				$items[$row['page_id']]['titles'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$items[$row['page_id']]['params'][$row['name']] = $row['value'];

			if (!empty($row['keyword']))
				$items[$row['page_id']]['keywords'][] = $row['keyword'];

			if (!empty($row['message'])) {
				$items[$row['page_id']]['comments'][$row['id']] = array(
					'id'         => $row['id'],
					'parent_id'  => $row['parent_id'],
					'author_id'  => $row['com_author_id'],
					'message'    => trim($row['message']),
					'created_at' => $row['com_created_at']
				);
			}
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
	protected function getXmlFile()
	{
		if (empty($items = $this->getData()))
			return '';

		$xml = new \DomDocument('1.0', 'utf-8');
		$root = $xml->appendChild($xml->createElement('light_portal'));

		$xml->formatOutput = true;

		$xmlElements = $root->appendChild($xml->createElement('pages'));
		foreach ($items as $item) {
			$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
			foreach ($item as $key => $val) {
				$xmlName = $xmlElement->appendChild(
					in_array($key, ['page_id', 'author_id', 'permissions', 'status', 'num_views', 'num_comments', 'created_at', 'updated_at'])
						? $xml->createAttribute($key)
						: $xml->createElement($key)
				);

				if (in_array($key, ['titles', 'params'])) {
					foreach ($item[$key] as $k => $v) {
						$xmlTitle = $xmlName->appendChild($xml->createElement($k));
						$xmlTitle->appendChild($xml->createTextNode($v));
					}
				} elseif (in_array($key, ['description', 'content'])) {
					$xmlName->appendChild($xml->createCDATASection($val));
				} elseif ($key == 'keywords' && !empty($val)) {
					$xmlName->appendChild($xml->createTextNode(implode(', ', array_unique($val))));
				} elseif ($key == 'comments') {
					foreach ($item[$key] as $k => $comment) {
						$xmlComment = $xmlName->appendChild($xml->createElement('comment'));
						foreach ($comment as $label => $text) {
							$xmlCommentElem = $xmlComment->appendChild($label == 'message' ? $xml->createElement($label) : $xml->createAttribute($label));
							$xmlCommentElem->appendChild($label == 'message' ? $xml->createCDATASection($text) : $xml->createTextNode($text));
						}
					}
				} else {
					$xmlName->appendChild($xml->createTextNode($val));
				}
			}
		}

		$file = sys_get_temp_dir() . '/lp_pages_backup.xml';
		$xml->save($file);

		return $file;
	}
}
