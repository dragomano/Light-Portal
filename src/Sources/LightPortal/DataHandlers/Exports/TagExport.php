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
use Bugo\LightPortal\Repositories\TagRepositoryInterface;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;

if (! defined('SMF'))
	die('No direct access...');

class TagExport extends XmlExporter
{
	use ExportTableTrait;

	protected string $entity = 'tags';

	public function __construct(
		private readonly TagRepositoryInterface $repository,
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

		$query = '
			SELECT tag.*, pt.page_id, t.lang, COALESCE(t.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				LEFT JOIN {db_prefix}lp_page_tag AS pt ON (tag.tag_id = pt.tag_id)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag}
				)
			WHERE 1=1' . (empty($tags) ? '' : '
				AND tag.tag_id IN ({array_int:tags})');

		$params = [
			'empty_string' => '',
			'tags'         => $tags,
		];

		$result = $this->db->query($query, $params);

		$items = [];
		while ($row = $this->db->fetchAssoc($result)) {
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

		$this->db->freeResult($result);

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
				'type' => 'element',
				'useCDATA' => false
			],
			'pages' => [
				'type' => 'subitem',
				'elementName' => 'page',
				'subFields' => [
					'id' => [
						'isAttribute' => true,
						'useCDATA' => false
					]
				]
			]
		];
	}
}
