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

namespace LightPortal\Repositories;

interface PageRepositoryInterface extends RepositoryInterface
{
	public function getData(int|string $item): array;

	public function restore(array $items): void;

	public function removePermanently(array $items): void;

	public function getPrevNextLinks(array $page, bool $withinCategory = false): array;

	public function getRelatedPages(array $page): array;

	public function updateNumViews(int $item): void;

	public function getMenuItems(): array;

	public function prepareData(?array &$data): void;

	public function fetchTags(array $pageIds): iterable;
}
