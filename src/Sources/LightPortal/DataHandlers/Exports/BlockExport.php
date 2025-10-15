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

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Repositories\BlockRepositoryInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Exception;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('SMF'))
	die('No direct access...');

class BlockExport extends XmlExporter
{
	protected string $entity = 'blocks';

	public function __construct(
		private readonly BlockRepositoryInterface $repository,
		PortalSqlInterface $sql,
		FilesystemInterface $filesystem,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($this->entity, $sql, $filesystem, $errorHandler);
	}

	public function main(): void
	{
		parent::main();

		Utils::$context['lp_current_blocks'] = $this->repository->getAll(0, 0, 'placement DESC, priority');
	}

	protected function setupUi(): void
	{
		parent::setupUi();

		Theme::loadTemplate('LightPortal/ManageImpex');

		Utils::$context['sub_template'] = 'manage_export_blocks';
	}

	protected function getData(): array
	{
		if ($this->isEntityEmpty())
			return [];

		$blocks = $this->hasEntityInRequest() ? $this->request()->get($this->entity) : [];

		try {
			$select = $this->sql->select()
				->from(['b' => 'lp_blocks'])
				->join(
					['t' => 'lp_translations'],
					new Expression('b.block_id = t.item_id AND t.type = ?', ['block']),
					[
						'lang'        => new Expression('t.lang'),
						'title'       => new Expression('COALESCE(t.title, "")'),
						'content'     => new Expression('COALESCE(t.content, "")'),
						'description' => new Expression('COALESCE(t.description, "")'),
					],
					Select::JOIN_LEFT
				)
				->join(
					['p' => 'lp_params'],
					new Expression('b.block_id = p.item_id AND p.type = ?', ['block']),
					[
						'name'  => new Expression('p.name'),
						'value' => new Expression('p.value'),
					],
					Select::JOIN_LEFT
				);

			if ($blocks !== []) {
				$select->where->in('b.block_id', $blocks);
			}

			$result = $this->sql->execute($select);

			$items = [];
			foreach ($result as $row) {
				$items[$row['block_id']] ??= [
					'block_id'      => $row['block_id'],
					'icon'          => $row['icon'],
					'type'          => $row['type'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'status'        => $row['status'],
					'areas'         => $row['areas'],
					'title_class'   => $row['title_class'],
					'content_class' => $row['content_class'],
				];

				if ($row['lang'] && $row['title']) {
					$items[$row['block_id']]['titles'][$row['lang']] = trim($row['title']);
				}

				if ($row['lang'] && $row['content']) {
					$items[$row['block_id']]['contents'][$row['lang']] = trim($row['content']);
				}

				if ($row['lang'] && $row['description']) {
					$items[$row['block_id']]['descriptions'][$row['lang']] = trim($row['description']);
				}

				if ($row['name'] && $row['value']) {
					$items[$row['block_id']]['params'][$row['name']] = trim($row['value']);
				}
			}
		} catch (Exception) {
			return [];
		}

		return array_map(
			static fn($item) => array_filter($item, static fn($value) => $value !== null && $value !== ''),
			$items
		);
	}

	protected function getFile(): string
	{
		$items = $this->getData();

		return $this->createXmlFile($items);
	}

	protected function getAttributeFields(): array
	{
		return ['block_id', 'priority', 'permissions', 'status'];
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
		];
	}
}
