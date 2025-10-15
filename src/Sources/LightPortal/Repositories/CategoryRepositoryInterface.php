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

interface CategoryRepositoryInterface extends RepositoryInterface
{
	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $filter = '',
		array $whereConditions = []
	): array;

	public function getTotalCount(string $filter = '', array $whereConditions = []): int;

	public function updatePriority(array $categories = []): void;
}
