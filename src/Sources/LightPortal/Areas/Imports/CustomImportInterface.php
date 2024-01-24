<?php declare(strict_types=1);

/**
 * CustomImportInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Imports;

if (! defined('SMF'))
	die('No direct access...');

interface CustomImportInterface
{
	public function getAll(int $start, int $items_per_page, string $sort): array;

	public function getTotalCount(): int;
}