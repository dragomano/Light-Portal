<?php declare(strict_types=1);

/**
 * PageExport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\Repositories\PageRepository;
use ArrayIterator;
use DomDocument;
use DOMException;

if (! defined('SMF'))
	die('No direct access...');

final class PageExport extends AbstractExport
{
	private PageRepository $repository;

	public function __construct()
	{
		$this->repository = new PageRepository;
	}

	public function main()
	{
		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_export'];
		$this->context['page_area_title'] = $this->txt['lp_pages_export'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_pages;sa=export';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_export_description']
		];

		$this->run();

		$listOptions = [
			'id' => 'lp_pages',
			'items_per_page' => 20,
			'title' => $this->txt['lp_pages_export'],
			'no_items_label' => $this->txt['lp_no_items'],
			'base_href' => $this->scripturl . '?action=admin;area=lp_pages;sa=export',
			'default_sort_col' => 'id',
			'get_items' => [
				'function' => [$this->repository, 'getAll']
			],
			'get_count' => [
				'function' => [$this->repository, 'getTotalCount']
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
						'default' => 'p.page_id',
						'reverse' => 'p.page_id DESC'
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
						'default' => 'p.alias DESC',
						'reverse' => 'p.alias'
					]
				],
				'title' => [
					'header' => [
						'value' => $this->txt['lp_title']
					],
					'data' => [
						'function' => fn($entry) => '<a class="bbc_link' . (
							$entry['is_front']
								? ' new_posts" href="' . $this->scripturl
								: '" href="' . LP_PAGE_URL . $entry['alias']
							) . '">' . $entry['title'] . '</a>',
						'class' => 'word_break'
					],
					'sort' => [
						'default' => 't.title DESC',
						'reverse' => 't.title'
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
				'href' => $this->scripturl . '?action=admin;area=lp_pages;sa=export'
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="export_selection" value="' . $this->txt['lp_export_selection'] . '" class="button">
						<input type="submit" name="export_all" value="' . $this->txt['lp_export_all'] . '" class="button">'
				]
			]
		];

		$this->createList($listOptions);
	}

	protected function getData(): array
	{
		if ($this->request()->isEmpty('pages') && $this->request()->hasNot('export_all'))
			return [];

		$pages = $this->request('pages') && $this->request()->hasNot('export_all') ? $this->request('pages') : null;

		$request = $this->smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions, p.status, p.num_views, p.num_comments, p.created_at, p.updated_at,
				pt.lang, pt.title, pp.name, pp.value, com.id, com.parent_id, com.author_id AS com_author_id, com.message, com.created_at AS com_created_at
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
				LEFT JOIN {db_prefix}lp_comments AS com ON (p.page_id = com.page_id)' . (empty($pages) ? '' : '
			WHERE p.page_id IN ({array_int:pages})'),
			[
				'pages' => $pages
			]
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['page_id']] ??= [
				'page_id'      => $row['page_id'],
				'category_id'  => $row['category_id'],
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
			];

			if ($row['lang'] && $row['title'])
				$items[$row['page_id']]['titles'][$row['lang']] = $row['title'];

			if ($row['name'] && $row['value'])
				$items[$row['page_id']]['params'][$row['name']] = $row['value'];

			if ($row['message'] && trim($row['message'])) {
				$items[$row['page_id']]['comments'][$row['id']] = [
					'id'         => $row['id'],
					'parent_id'  => $row['parent_id'],
					'author_id'  => $row['com_author_id'],
					'message'    => trim($row['message']),
					'created_at' => $row['com_created_at']
				];
			}
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	protected function getCategories(): array
	{
		$categories = $this->getEntityList('category');

		unset($categories[0]);
		ksort($categories);

		return $categories;
	}

	protected function getXmlFile(): string
	{
		if (empty($items = $this->getData()))
			return '';

		try {
			$xml = new DomDocument('1.0', 'utf-8');
			$root = $xml->appendChild($xml->createElement('light_portal'));

			$xml->formatOutput = true;

			if ($categories = $this->getCategories()) {
				$xmlElements = $root->appendChild($xml->createElement('categories'));

				$categories = fn() => new ArrayIterator($categories);
				foreach ($categories() as $category) {
					$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
					foreach ($category as $key => $val) {
						$xmlName = $xmlElement->appendChild($xml->createAttribute($key));
						$xmlName->appendChild($xml->createTextNode($val));
					}
				}
			}

			if ($tags = $this->getEntityList('tag')) {
				$xmlElements = $root->appendChild($xml->createElement('tags'));

				$tags = fn() => new ArrayIterator($tags);
				foreach ($tags() as $key => $val) {
					$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
					$xmlName = $xmlElement->appendChild($xml->createAttribute('id'));
					$xmlName->appendChild($xml->createTextNode((string) $key));
					$xmlName = $xmlElement->appendChild($xml->createAttribute('value'));
					$xmlName->appendChild($xml->createTextNode($val));
				}
			}

			$xmlElements = $root->appendChild($xml->createElement('pages'));
			$items = fn() => new ArrayIterator($items);

			foreach ($items() as $item) {
				$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
				foreach ($item as $key => $val) {
					$xmlName = $xmlElement->appendChild(
						in_array($key, ['page_id', 'category_id', 'author_id', 'permissions', 'status', 'num_views', 'num_comments', 'created_at', 'updated_at'])
							? $xml->createAttribute($key)
							: $xml->createElement($key)
					);

					if (in_array($key, ['titles', 'params'])) {
						foreach ($val as $k => $v) {
							$xmlTitle = $xmlName->appendChild($xml->createElement($k));
							$xmlTitle->appendChild($xml->createTextNode($v));
						}
					} elseif (in_array($key, ['description', 'content'])) {
						$xmlName->appendChild($xml->createCDATASection($val));
					} elseif ($key == 'comments') {
						foreach ($val as $comment) {
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
		} catch (DOMException $e) {
			$this->logError('[LP] ' . $this->txt['lp_pages_export'] . ': ' . $e->getMessage());
		}

		return $file ?? '';
	}
}
