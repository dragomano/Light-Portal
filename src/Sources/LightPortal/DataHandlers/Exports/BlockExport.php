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
use Bugo\LightPortal\Repositories\BlockRepositoryInterface;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

class BlockExport extends XmlExporter
{
	protected string $entity = 'blocks';

	public function __construct(
		private readonly BlockRepositoryInterface $repository,
		DatabaseInterface $database,
		FilesystemInterface $filesystem,
		ErrorHandlerInterface $errorHandler
	) {
		parent::__construct($this->entity, $database, $filesystem, $errorHandler);
	}

	public function main(): void
	{
		parent::main();

		Utils::$context['lp_current_blocks'] = $this->repository->getAll();
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
			$result = $this->db->query('
				SELECT
					b.*, pt.lang, pp.name, pp.value,
					COALESCE(pt.title, {string:empty_string}) AS title,
					COALESCE(pt.content, {string:empty_string}) AS content,
					COALESCE(pt.description, {string:empty_string}) AS description
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_translations AS pt ON (
						b.block_id = pt.item_id AND pt.type = {literal:block}
					)
					LEFT JOIN {db_prefix}lp_params AS pp ON (
						b.block_id = pp.item_id AND pp.type = {literal:block}
					)
				WHERE 1=1' . (empty($blocks) ? '' : '
					AND b.block_id IN ({array_int:blocks})'),
				[
					'empty_string' => '',
					'blocks'       => $blocks,
				]
			);

			$items = [];
			while ($row = $this->db->fetchAssoc($result)) {
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

			$this->db->freeResult($result);
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
				'type' => 'element',
				'useCDATA' => false
			],
			'params' => [
				'type' => 'element',
				'useCDATA' => false
			],
			'contents' => [
				'type' => 'element',
				'useCDATA' => true
			],
			'descriptions' => [
				'type' => 'element',
				'useCDATA' => true
			]
		];
	}
}
