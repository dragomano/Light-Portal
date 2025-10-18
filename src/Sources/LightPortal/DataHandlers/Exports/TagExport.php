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
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasUiTable;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;

if (! defined('SMF'))
	die('No direct access...');

class TagExport extends XmlExporter
{
	use HasUiTable;

	protected string $entity = 'tags';

	public function __construct(
		private readonly TagRepositoryInterface $repository,
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
			IdColumn::make()->setSort('tag_id'),
			IconColumn::make(),
			TitleColumn::make(entity: $this->entity),
			CheckboxColumn::make(entity: $this->entity)
		];
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty())
			return [];

		$tags = $this->hasEntityInRequest() ? $this->request()->get($this->entity) : [];

		$select = $this->sql->select()
			->from(['tag' => 'lp_tags'])
			->join(
				['pt' => 'lp_page_tag'],
				'tag.tag_id = pt.tag_id',
				['page_id' => new Expression('pt.page_id')],
				Select::JOIN_LEFT
			)
			->join(
				['t' => 'lp_translations'],
				new Expression('tag.tag_id = t.item_id AND t.type = ?', ['tag']),
				[
					'lang'  => new Expression('t.lang'),
					'title' => new Expression('COALESCE(t.title, "")'),
				],
				Select::JOIN_LEFT
			);

		if ($tags !== []) {
			$select->where->in('tag.tag_id', $tags);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['tag_id']] ??= [
				'tag_id' => $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => trim($row['icon'] ?? ''),
				'status' => $row['status'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['tag_id']]['titles'][$row['lang']] = trim($row['title']);
			}

			if ($row['page_id']) {
				$items[$row['tag_id']]['pages'][$row['page_id']] = [
					'id' => $row['page_id'],
				];
			}
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
		return ['tag_id', 'status'];
	}

	protected function getNestedFieldRules(): array
	{
		return [
			'titles' => [
				'type'     => 'element',
				'useCDATA' => false,
			],
			'pages' => [
				'type'        => 'subitem',
				'elementName' => 'page',
				'subFields'   => [
					'id' => [
						'isAttribute' => true,
						'useCDATA'    => false,
					]
				]
			]
		];
	}
}
