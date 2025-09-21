<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\DataHandlers\Exports;

use Bugo\Bricks\Tables\IdColumn;
use Bugo\LightPortal\Repositories\CategoryRepositoryInterface;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

class CategoryExport extends XmlExporter
{
	use ExportTableTrait;

	protected string $entity = 'categories';

	public function __construct(
		private readonly CategoryRepositoryInterface $repository,
		DatabaseInterface $database,
		FilesystemInterface $filesystem,
		ErrorHandlerInterface $errorHandler
	) {
		parent::__construct($this->entity, $database, $filesystem, $errorHandler);
	}

	protected function setupUi(): void
	{
		parent::setupUi();

		$this->addUiTable();
	}

	protected function defineUiColumns(): array
	{
		return [
			IdColumn::make()->setSort('category_id'),
			IconColumn::make(),
			TitleColumn::make(entity: $this->entity),
			CheckboxColumn::make(entity: $this->entity)
		];
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty()) {
			return [];
		}

		$categories = $this->hasEntityInRequest() ? $this->request()->get($this->entity) : [];

		try {
			$result = $this->db->query('
				SELECT
					c.*, pt.lang,
					COALESCE(pt.title, {string:empty_string}) AS title,
					COALESCE(pt.description, {string:empty_string}) AS description
				FROM {db_prefix}lp_categories AS c
					LEFT JOIN {db_prefix}lp_translations AS pt ON (
						c.category_id = pt.item_id AND pt.type = {literal:category}
					)
				WHERE 1=1' . (empty($categories) ? '' : '
					AND c.category_id IN ({array_int:categories})'),
				[
					'empty_string' => '',
					'categories'   => $categories,
				]
			);

			$items = [];
			while ($row = $this->db->fetchAssoc($result)) {
				if (! isset($row['category_id'])) {
					continue;
				}

				$categoryId = $row['category_id'];

				if (! isset($items[$categoryId])) {
					$items[$categoryId] = [
						'category_id' => $row['category_id'],
						'parent_id'   => $row['parent_id'] ?? '0',
						'slug'        => $row['slug'] ?? '',
						'icon'        => trim($row['icon'] ?? ''),
						'priority'    => $row['priority'] ?? 0,
						'status'      => $row['status'] ?? 0,
					];
				}

				if ($row['lang'] && $row['title']) {
					$items[$categoryId]['titles'][$row['lang']] = trim($row['title']);
				}

				if ($row['lang'] && $row['description']) {
					$items[$categoryId]['descriptions'][$row['lang']] = trim($row['description']);
				}
			}

			$this->db->freeResult($result);
		} catch (Exception) {
			return [];
		}

		return $items;
	}

	protected function getFile(): string
	{
		$items = $this->getData();

		return $this->createXmlFile($items);
	}

	protected function getAttributeFields(): array
	{
		return ['category_id', 'parent_id', 'priority', 'status'];
	}

	protected function getNestedFieldRules(): array
	{
		return [
			'titles' => [
				'type' => 'element',
				'useCDATA' => false
			],
			'descriptions' => [
				'type' => 'element',
				'useCDATA' => true
			]
		];
	}
}
