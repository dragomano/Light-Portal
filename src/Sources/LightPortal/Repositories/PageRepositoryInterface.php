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

interface PageRepositoryInterface extends RepositoryInterface
{
	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $queryString = '',
		array $queryParams = []
	): array;

	public function getTotalCount(string $queryString = '', array $queryParams = []): int;

	public function getData(int|string $item): array;

	public function setData(int $item = 0): void;

	public function remove(array $items): void;

	public function restore(array $items): void;

	public function removePermanently(array $items): void;

	public function getPrevNextLinks(array $page, bool $withinCategory = false): array;

	public function getRelatedPages(array $page): array;

	public function updateNumViews(int $item): void;

	public function getMenuItems(): array;

	public function prepareData(?array &$data): void;
}
