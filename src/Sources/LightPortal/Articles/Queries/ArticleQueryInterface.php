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

namespace LightPortal\Articles\Queries;

interface ArticleQueryInterface
{
	public function init(array $params): void;

	public function setSorting(?string $sortType): void;

	public function getSorting(): string;

	public function prepareParams(int $start, int $limit): void;

	public function getRawData(): iterable;

	public function getTotalCount(): int;
}
