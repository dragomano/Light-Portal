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

namespace LightPortal\DataHandlers\Imports;

use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;
use SimpleXMLElement;

if (! defined('SMF'))
	die('No direct access...');

abstract class XmlImporter extends AbstractImport
{
	public function __construct(
		protected string $entity,
		PortalSqlInterface $sql,
		FileInterface $file,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($sql, $file, $errorHandler);
	}

	public function getEntity(): string
	{
		return $this->entity;
	}

	abstract protected function processItems(): void;

	protected function parseXml(): bool
	{
		$this->xml = $this->getFile();

		if ($this->xml === false)
			return false;

		if (! ($this->xml instanceof SimpleXMLElement)) {
			$this->errorHandler->fatal('lp_wrong_import_file', false);
		}

		if (! isset($this->xml->{$this->entity})) {
			$this->errorHandler->fatal('lp_wrong_import_file', false);
		}

		$entityElement = $this->xml->{$this->entity};
		if (empty($entityElement->item)) {
			$this->errorHandler->fatal('lp_wrong_import_file', false);
		}

		return true;
	}

	protected function run(): void
	{
		if (! $this->parseXml())
			return;

		$this->processItems();
	}

	protected function extractTranslations(SimpleXMLElement $item, int $id): array
	{
		$translations = [];

		$map = [
			'titles'       => 'title',
			'contents'     => 'content',
			'descriptions' => 'description',
		];

		foreach ($map as $key => $singular) {
			if (isset($item->{$key})) {
				foreach ($item->{$key}->children() as $lang => $textElement) {
					$text = (string) $textElement;
					$this->addTranslation($translations, $lang, $id, $singular, $text);
				}
			}
		}

		return $translations;
	}

	protected function extractParams(SimpleXMLElement $item, int $id): array
	{
		$params = [];

		if ($item->params ?? null) {
			foreach ($item->params->children() as $key => $value) {
				$params[] = [
					'item_id' => $id,
					'type'    => $this->getEntityType(),
					'name'    => $key,
					'value'   => trim((string) $value),
				];
			}
		}

		return $params;
	}

	protected function addTranslation(
		array &$translations,
		string $lang,
		int $itemId,
		string $field,
		string $text
	): void
	{
		$key = $lang . '_' . $itemId;

		if (! isset($translations[$key])) {
			$translations[$key] = [
				'item_id'     => $itemId,
				'type'        => $this->getEntityType(),
				'lang'        => $lang,
				'title'       => '',
				'content'     => '',
				'description' => '',
			];
		}

		$translations[$key][$field] = trim($text);
	}

	protected function getEntityType(): string
	{
		return match ($this->entity) {
			'blocks'     => 'block',
			'categories' => 'category',
			'pages'      => 'page',
			'tags'       => 'tag',
			default      => $this->entity,
		};
	}
}
