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
use Bugo\Compat\Config;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\DataHandlers\Traits\HasUiTable;
use Bugo\LightPortal\Repositories\PageRepositoryInterface;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\PageSlugColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('SMF'))
	die('No direct access...');

class PageExport extends XmlExporter
{
	use HasUiTable;

	protected string $entity = 'pages';

	public function __construct(
		private readonly PageRepositoryInterface $repository,
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
			IdColumn::make()->setSort('p.page_id'),
			PageSlugColumn::make(),
			TitleColumn::make(entity: $this->entity)->setData(static fn($entry) => Str::html('a', [
				'class' => 'bbc_link' . ($entry['is_front'] ? ' new_posts' : ''),
				'href'  => $entry['is_front'] ? Config::$scripturl : (LP_PAGE_URL . $entry['slug']),
			])->setText($entry['title'])),
			CheckboxColumn::make(entity: $this->entity)
		];
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty())
			return [];

		$pages = $this->hasEntityInRequest() ? $this->request()->get($this->entity) : [];

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->join(
				['t' => 'lp_translations'],
				new Expression('p.page_id = t.item_id AND t.type = ?', ['page']),
				[
					'lang'        => new Expression('t.lang'),
					'title'       => new Expression('COALESCE(t.title, "")'),
					'content'     => new Expression('COALESCE(t.content, "")'),
					'description' => new Expression('COALESCE(t.description, "")'),
				],
				Select::JOIN_LEFT
			)
			->join(
				['pp' => 'lp_params'],
				new Expression('p.page_id = pp.item_id AND pp.type = ?', ['page']),
				[
					'name'  => new Expression('pp.name'),
					'value' => new Expression('pp.value'),
				],
				Select::JOIN_LEFT
			)
			->join(
				['com' => 'lp_comments'],
				'p.page_id = com.page_id',
				[
					'id'             => new Expression('com.id'),
					'parent_id'      => new Expression('com.parent_id'),
					'com_author_id'  => new Expression('com.author_id'),
					'message'        => new Expression('com.message'),
					'com_created_at' => new Expression('com.created_at'),
				],
				Select::JOIN_LEFT
			);

		if ($pages !== []) {
			$select->where->in('p.page_id', $pages);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['page_id']] ??= [
				'page_id'         => $row['page_id'],
				'category_id'     => $row['category_id'],
				'author_id'       => $row['author_id'],
				'slug'            => $row['slug'],
				'type'            => $row['type'],
				'entry_type'      => $row['entry_type'],
				'permissions'     => $row['permissions'],
				'status'          => $row['status'],
				'num_views'       => $row['num_views'],
				'num_comments'    => $row['num_comments'],
				'created_at'      => $row['created_at'],
				'updated_at'      => $row['updated_at'],
				'deleted_at'      => $row['deleted_at'],
				'last_comment_id' => $row['last_comment_id'],
			];

			if ($row['lang'] && $row['title']) {
				$items[$row['page_id']]['titles'][$row['lang']] = trim($row['title']);
			}

			if ($row['lang'] && $row['content']) {
				$items[$row['page_id']]['contents'][$row['lang']] = trim($row['content']);
			}

			if ($row['lang'] && $row['description']) {
				$items[$row['page_id']]['descriptions'][$row['lang']] = trim($row['description']);
			}

			if ($row['name'] && $row['value']) {
				$items[$row['page_id']]['params'][$row['name']] = trim($row['value']);
			}

			if ($row['message'] && trim($row['message'])) {
				$items[$row['page_id']]['comments'][$row['id']] = [
					'id'         => $row['id'],
					'parent_id'  => $row['parent_id'],
					'author_id'  => $row['com_author_id'],
					'message'    => trim($row['message']),
					'created_at' => $row['com_created_at'],
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
		return [
			'page_id', 'category_id', 'author_id', 'permissions', 'status', 'num_views',
			'num_comments', 'created_at', 'updated_at', 'deleted_at', 'last_comment_id',
		];
	}

	protected function getNestedFieldRules(): array
	{
		return [
			'titles' => [
				'type'     => 'element',
				'useCDATA' => false,
			],
			'contents' => [
				'type'     => 'element',
				'useCDATA' => true,
			],
			'descriptions' => [
				'type'     => 'element',
				'useCDATA' => true,
			],
			'params' => [
				'type'     => 'element',
				'useCDATA' => false,
			],
			'comments' => [
				'type'        => 'subitem',
				'elementName' => 'comment',
				'subFields'   => [
					'id' => [
						'isAttribute' => true,
						'useCDATA'    => false,
					],
					'parent_id' => [
						'isAttribute' => true,
						'useCDATA'    => false,
					],
					'author_id' => [
						'isAttribute' => true,
						'useCDATA'    => false,
					],
					'message' => [
						'isAttribute' => false,
						'useCDATA'    => true,
					],
					'created_at' => [
						'isAttribute' => true,
						'useCDATA'    => false,
					]
				]
			]
		];
	}
}
