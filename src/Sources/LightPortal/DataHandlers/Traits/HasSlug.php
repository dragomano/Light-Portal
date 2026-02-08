<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\DataHandlers\Traits;

trait HasSlug
{
	protected function initializeSlugAndTranslations($item, int $entityId, array &$titles): string
	{
		$slug = isset($item->slug) ? (string) $item->slug : '';
		if (empty($slug)) {
			$slug = 'temp-' . $entityId;
		}

		$itemTranslations = $this->extractTranslations($item, $entityId);
		foreach ($itemTranslations as $translation) {
			if (isset($translation['title'])) {
				$titles[$entityId][$translation['lang']] = $translation['title'];
			}
		}

		return $slug;
	}

	protected function updateSlugs(array &$items, array $titles, string $idKey): void
	{
		foreach ($items as &$item) {
			if (str_starts_with($item['slug'], 'temp-')) {
				$entityId = $item[$idKey];
				$entityTitles = $titles[$entityId] ?? [];
				$item['slug'] = $this->generateSlug($entityTitles);
			}
		}
	}
}
