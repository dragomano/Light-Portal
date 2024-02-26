<?php declare(strict_types=1);

/**
 * PageListInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

interface PageListInterface
{
	public const STATUS_INACTIVE = 0;

	public const STATUS_ACTIVE = 1;

	public function getPages(int $start, int $limit, string $sort): array;

	public function getTotalCount(): int;
}
