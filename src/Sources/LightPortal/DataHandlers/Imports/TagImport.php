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
use LightPortal\DataHandlers\Traits\HasSlug;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;
use SimpleXMLElement;

if (! defined('SMF'))
	die('No direct access...');

class TagImport extends XmlImporter
{
	use HasSlug;

	protected string $entity = 'tags';

	public function __construct(
		PortalSqlInterface $sql,
		FileInterface $file,
		ErrorHandlerInterface $errorHandler
	)
	{
		parent::__construct($this->entity, $sql, $file, $errorHandler);
	}

	protected function processItems(): void
	{
		$items = $translations = $pages = [];
		$tagTitles = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$tagId = intval($item['tag_id']);

			$slug = $this->initializeSlugAndTranslations($item, $tagId, $tagTitles);

			$items[] = [
				'tag_id' => $tagId,
				'slug'   => $slug,
				'icon'   => (string) $item->icon,
				'status' => intval($item['status']),
			];

			$translations = array_merge($translations, $this->extractTranslations($item, $tagId));
			$pages = array_merge($pages, $this->extractPages($item, $tagId));
		}

		$this->updateSlugs($items, $tagTitles, 'tag_id');

		$this->startTransaction($items);

		$results = $this->insertData('lp_tags', $items, ['tag_id'], true);
		$results = $this->replaceTranslations($translations, $results);
		$results = $this->replacePages($pages, $results);

		$this->finishTransaction($results);
	}

	protected function extractPages(SimpleXMLElement $item, int $id): array
	{
		$pages = [];

		if ($item->pages ?? null) {
			foreach ($item->pages->children() as $page) {
				$pages[] = [
					'page_id' => intval($page['id']),
					'tag_id'  => $id,
				];
			}
		}

		return $pages;
	}

	protected function replacePages(array $pages, array $results): array
	{
		if ($pages === [] || $results === [])
			return [];

		return $this->insertData('lp_page_tag', $pages, ['page_id', 'tag_id'], true);
	}
}
