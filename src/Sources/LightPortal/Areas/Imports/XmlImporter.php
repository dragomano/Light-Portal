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

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\User;
use SimpleXMLElement;

use function array_walk_recursive;
use function preg_match;
use function preg_replace;
use function reset;
use function rtrim;
use function strlen;
use function strtolower;
use function substr;
use function time;
use function trim;

use const LP_ALIAS_PATTERN;

if (! defined('SMF'))
	die('No direct access...');

abstract class XmlImporter extends AbstractImport
{
	protected string $entity;

	protected SimpleXMLElement|bool $xml;

	abstract protected function processItems(): void;

	protected function parseXml(): bool
	{
		$this->xml = $this->getFile();

		if ($this->xml === false)
			return false;

		if (! isset($this->xml->{$this->entity}->item[0])) {
			ErrorHandler::fatalLang('lp_wrong_import_file', false);
		}

		return true;
	}

	protected function run(): void
	{
		if (! $this->parseXml())
			return;

		$this->processItems();
	}

	protected function extractTranslations(SimpleXMLElement $item): array
	{
		$translations = [];
		$itemId = $this->getItemId($item);

		$map = [
			'titles'       => 'title',
			'contents'     => 'content',
			'descriptions' => 'description',
		];

		foreach ($map as $key => $singular) {
			$data = $item->{$key} ?? [];
			array_walk_recursive($data, function ($text, $lang) use (&$translations, $itemId, $singular) {
				$this->addTranslation($translations, $lang, $itemId, $singular, (string) $text);
			});
		}

		return $translations;
	}

	protected function extractParams(SimpleXMLElement $item): array
	{
		$params = [];
		$itemId = $this->getItemId($item);
		$entityType = $this->getEntityType();

		if ($item->params ?? null) {
			foreach ($item->params as $param) {
				foreach ($param as $k => $v) {
					$params[] = [
						'item_id' => $itemId,
						'type'    => $entityType,
						'name'    => $k,
						'value'   => (string) $v,
					];
				}
			}
		}

		return $params;
	}

	private function addTranslation(array &$translations, string $lang, string $itemId, string $field, string $text): void
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

		$translations[$key][$field] = $text;
	}

	protected function getItemId(SimpleXMLElement $item): string
	{
		return match ($this->entity) {
			'blocks'     => (string) ($item['block_id'] ?? ''),
			'categories' => (string) ($item['category_id'] ?? ''),
			'pages'      => (string) ($item['page_id'] ?? ''),
			'plugins'    => (string) ($item['plugin_id'] ?? ''),
			'tags'       => (string) ($item['tag_id'] ?? ''),
			default      => '',
		};
	}

	protected function getEntityType(): string
	{
		return match ($this->entity) {
			'blocks'     => 'block',
			'categories' => 'category',
			'pages'      => 'page',
			'plugins'    => 'plugin',
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
		$priority = ['english', Config::$language, User::$me->language];

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
