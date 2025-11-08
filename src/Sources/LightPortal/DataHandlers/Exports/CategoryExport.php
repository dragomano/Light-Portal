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

namespace LightPortal\DataHandlers\Exports;

use Bugo\Bricks\Tables\IdColumn;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasUiTable;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use Exception;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('SMF'))
	die('No direct access...');

class CategoryExport extends XmlExporter
{
	use HasUiTable;

	protected string $entity = 'categories';

	public function __construct(
		private readonly CategoryRepositoryInterface $repository,
		PortalSqlInterface $sql,
		FilesystemInterface $filesystem,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($this->entity, $sql, $filesystem, $errorHandler);
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
			$select = $this->sql->select()
				->from(['c' => 'lp_categories'])
				->join(
					['t' => 'lp_translations'],
					new Expression('c.category_id = t.item_id AND t.type = ?', ['category']),
					[
						'lang'        => new Expression('t.lang'),
						'title'       => new Expression("COALESCE(t.title, '')"),
						'description' => new Expression("COALESCE(t.description, '')"),
					],
					Select::JOIN_LEFT
				);

			if ($categories !== []) {
				$select->where->in('c.category_id', $categories);
			}

			$result = $this->sql->execute($select);

			$items = [];
			foreach ($result as $row) {
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
				'type'     => 'element',
				'useCDATA' => false,
			],
			'descriptions' => [
				'type'     => 'element',
				'useCDATA' => true,
			]
		];
	}
}
