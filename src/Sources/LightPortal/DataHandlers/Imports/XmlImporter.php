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

namespace Bugo\LightPortal\DataHandlers\Imports;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use SimpleXMLElement;

use const LP_ALIAS_PATTERN;

if (! defined('SMF'))
	die('No direct access...');

abstract class XmlImporter extends AbstractImport
{
	public function __construct(
		protected string $entity,
		FileInterface $file,
		DatabaseInterface $db,
		ErrorHandlerInterface $errorHandler)
	{
		parent::__construct($file, $db, $errorHandler);
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

	protected function generateSlug(array $titles): string
	{
		if (empty($titles)) {
			return $this->getShortPrefix() . '-' . $this->generateShortId();
		}

		$selectedTitle = $this->selectTitleByPriority($titles);
		$slug = $this->cleanAndFormatSlug($selectedTitle);

		return $slug ?: $this->getShortPrefix() . '-' . $this->generateShortId();
	}

	protected function selectTitleByPriority(array $titles): string
	{
		$priority = ['english', Config::$language ?? 'english', User::$me->language ?? 'english'];

		foreach ($priority as $lang) {
			if (isset($titles[$lang]) && ! empty(trim($titles[$lang]))) {
				return $titles[$lang];
			}
		}

		return reset($titles) ?: '';
	}

	protected function cleanAndFormatSlug(string $text): string
	{
		$slug = strtolower(trim($text));
		$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
		$slug = preg_replace('/\s+/', '-', $slug);
		$slug = preg_replace('/-+/', '-', $slug);
		$slug = trim($slug, '-');

		if (! preg_match('/' . LP_ALIAS_PATTERN . '/', $slug)) {
			$slug = $this->getPrefixForEntity() . $slug;
		}

		if (strlen($slug) > 255) {
			$slug = substr($slug, 0, 255);
			$slug = rtrim($slug, '-');
		}

		return $slug;
	}

	protected function getPrefixForEntity(bool $full = false): string
	{
		return match ($this->entity) {
			'categories' => $full ? 'category' : 'cat-',
			'tags'       => $full ? 'tag' : 'tag-',
			'pages'      => $full ? 'page' : 'page-',
			default      => $full ? 'item' : 'item-',
		};
	}

	protected function getShortPrefix(): string
	{
		return match ($this->entity) {
			'categories' => 'cat',
			'tags'       => 'tag',
			'pages'      => 'page',
			default      => 'item',
		};
	}

	protected function generateShortId(): string
	{
		return substr((string) time(), -6);
	}
}
