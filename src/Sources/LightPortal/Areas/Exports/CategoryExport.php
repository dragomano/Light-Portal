<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Compat\{Config, Db, ErrorHandler};
use Bugo\Compat\{Lang, Sapi, Utils};
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\Utils\ItemList;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Str;
use DomDocument;
use DOMException;

use function in_array;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryExport extends AbstractExport
{
	use RequestTrait;

	private readonly CategoryRepository $repository;

	public function __construct()
	{
		$this->repository = new CategoryRepository();
	}

	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_export'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_export'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_categories;sa=export';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_export_description'],
		];

		$this->run();

		$listOptions = [
			'id' => 'lp_categories',
			'items_per_page' => 20,
			'title' => Lang::$txt['lp_categories_export'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
			'default_sort_col' => 'id',
			'get_items' => [
				'function' => $this->repository->getAll(...)
			],
			'get_count' => [
				'function' => $this->repository->getTotalCount(...)
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
						'default' => 'category_id',
						'reverse' => 'category_id DESC'
					]
				],
				'icon' => [
					'header' => [
						'value' => Lang::$txt['custom_profile_icon']
					],
					'data' => [
						'db'    => 'icon',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'icon',
						'reverse' => 'icon DESC'
					]
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => static fn($entry) => $entry['status']
							? Str::html('a', ['class' => 'bbc_link'])
								->href(LP_BASE_URL . ';sa=categories;id=' . $entry['id'])
								->setText($entry['title'])
							: $entry['title'],
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 't.value DESC',
						'reverse' => 't.value',
					],
				],
				'actions' => [
					'header' => [
						'value' => Str::html('input', [
							'type' => 'checkbox',
							'onclick' => 'invertAll(this, this.form);',
						])
					],
					'data' => [
						'function' => static fn($entry) => Str::html('input', [
							'type' => 'checkbox',
							'value' => $entry['id'],
							'name' => 'categories[]',
						]),
						'class' => 'centertext'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['form_action']
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => Str::html('input', [
							'type' => 'hidden',
						]) .
						Str::html('input', [
							'type' => 'submit',
							'name' => 'export_selection',
							'value' => Lang::$txt['lp_export_selection'],
							'class' => 'button',
						]) .
						Str::html('input', [
							'type' => 'submit',
							'name' => 'export_all',
							'value' => Lang::$txt['lp_export_all'],
							'class' => 'button',
						])
				]
			]
		];

		new ItemList($listOptions);
	}

	protected function getData(): array
	{
		if ($this->request()->isEmpty('categories') && $this->request()->hasNot('export_all'))
			return [];

		$categories = $this->request('categories') && $this->request()->hasNot('export_all') ? $this->request('categories') : null;

		$result = Db::$db->query('', '
			SELECT c.category_id, c.icon, c.description, c.priority, c.status,	pt.lang, pt.value AS title
			FROM {db_prefix}lp_categories AS c
				LEFT JOIN {db_prefix}lp_titles AS pt ON (c.category_id = pt.item_id AND pt.type = {literal:category})' . (empty($categories) ? '' : '
			WHERE c.category_id IN ({array_int:categories})'),
			[
				'categories' => $categories,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['category_id']] ??= [
				'category_id' => $row['category_id'],
				'icon'        => trim($row['icon'] ?? ''),
				'description' => trim($row['description'] ?? ''),
				'priority'    => $row['priority'],
				'status'      => $row['status'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['category_id']]['titles'][$row['lang']] = $row['title'];
			}
		}

		Db::$db->free_result($result);

		return $items;
	}

	protected function getFile(): string
	{
		if (empty($items = $this->getData()))
			return '';

		try {
			$xml = new DomDocument('1.0', 'utf-8');
			$root = $xml->appendChild($xml->createElement('light_portal'));

			$xml->formatOutput = true;

			$xmlElements = $root->appendChild($xml->createElement('categories'));

			$items = $this->getGeneratorFrom($items);

			foreach ($items() as $item) {
				$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
				foreach ($item as $key => $val) {
					$xmlName = $xmlElement->appendChild(
						in_array($key, ['category_id', 'priority', 'status'])
							? $xml->createAttribute($key)
							: $xml->createElement($key)
					);

					if ($key === 'titles') {
						foreach ($val as $k => $v) {
							$xmlTitle = $xmlName->appendChild($xml->createElement($k));
							$xmlTitle->appendChild($xml->createTextNode($v));
						}
					} elseif ($key === 'description') {
						$xmlName->appendChild($xml->createCDATASection($val));
					} else {
						$xmlName->appendChild($xml->createTextNode($val));
					}
				}
			}

			$file = Sapi::getTempDir() . '/lp_categories_backup.xml';
			$xml->save($file);
		} catch (DOMException $e) {
			ErrorHandler::log('[LP] ' . Lang::$txt['lp_categories_export'] . ': ' . $e->getMessage(), 'user');
		}

		return $file ?? '';
	}
}
