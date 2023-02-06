<?php declare(strict_types=1);

/**
 * OtherImportInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Impex;

if (! defined('SMF'))
	die('No direct access...');

interface OtherImportInterface
{
	public function getAll(int $start, int $items_per_page, string $sort): array;

	public function getTotalCount(): int;
}