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

namespace Bugo\LightPortal\Repositories;

interface PageListRepositoryInterface
{
	public function getPagesByCategory(int $categoryId, int $start, int $limit, string $sort): array;

	public function getTotalPagesByCategory(int $categoryId): int;

	public function getCategoriesWithPageCount(int $start, int $limit, string $sort): array;

	public function getTotalCategoriesWithPages(): int;

	public function getPagesByTag(int $tagId, int $start, int $limit, string $sort): array;

	public function getTotalPagesByTag(int $tagId): int;

	public function getTagsWithPageCount(int $start, int $limit, string $sort): array;

	public function getTotalTagsWithPages(): int;
}
