<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Sapi;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ExportButtonsRow;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use DomDocument;
use DOMException;

use function in_array;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryExport extends AbstractExport
{
	private readonly CategoryRepository $repository;

	public function __construct()
	{
		$this->repository = app('category_repo');
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

		TablePresenter::show(
			PortalTableBuilder::make('lp_categories', Lang::$txt['lp_categories_export'])
				->setDefaultSortColumn('id')
				->setItems($this->repository->getAll(...))
				->setCount($this->repository->getTotalCount(...))
				->addColumns([
					IdColumn::make()->setSort('category_id'),
					IconColumn::make(),
					TitleColumn::make(entity: 'categories'),
					CheckboxColumn::make(entity: 'categories')
				])
				->addRows([
					ExportButtonsRow::make()
						->setPosition(RowPosition::ABOVE_COLUMN_HEADERS),
					ExportButtonsRow::make()
				])
		);
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
