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

if (! defined('SMF'))
	die('No direct access...');

class CategoryImport extends XmlImporter
{
	use HasSlug;

	protected string $entity = 'categories';

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
		$items = $translations = [];
		$categoryTitles = [];

		foreach ($this->xml->{$this->entity}->item as $item) {
			$categoryId = intval($item['category_id']);

			$slug = $this->initializeSlugAndTranslations($item, $categoryId, $categoryTitles);

			$items[] = [
				'category_id' => $categoryId,
				'parent_id'   => intval($item['parent_id']),
				'slug'        => $slug,
				'icon'        => (string) $item->icon,
				'priority'    => intval($item['priority']),
				'status'      => intval($item['status']),
			];

			$translations = array_merge($translations, $this->extractTranslations($item, $categoryId));
		}

		$this->updateSlugs($items, $categoryTitles, 'category_id');

		$this->startTransaction($items);

		$this->insertData('lp_categories', $items, ['category_id'], true);
		$this->replaceTranslations($translations);

		$this->finishTransaction();
	}
}
