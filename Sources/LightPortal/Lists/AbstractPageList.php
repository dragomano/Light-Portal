<?php

declare(strict_types = 1);

/**
 * PageListInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPageList implements PageListInterface
{
	use Helper;

	abstract public function show();

	abstract public function getPages(int $start, int $items_per_page, string $sort): array;

	abstract public function getTotalCountPages(): int;

	abstract public function showAll();

	abstract public function getList(): array;

	abstract public function getAll(int $start, int $items_per_page, string $sort): array;
}
