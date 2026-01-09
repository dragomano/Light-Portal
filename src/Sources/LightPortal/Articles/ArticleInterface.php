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

namespace LightPortal\Articles;

interface ArticleInterface
{
	public function init(): void;

	public function getSortingOptions(): array;

	public function getData(int $start, int $limit, ?string $sortType): iterable;

	public function getTotalCount(): int;
}
