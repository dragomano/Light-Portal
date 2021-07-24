<?php

namespace Bugo\LightPortal\Lists;

/**
 * PageListInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

interface PageListInterface
{
	public function show();
	public function getPages(int $start, int $items_per_page, string $sort): array;
	public function getTotalCountPages(): int;
	public function showAll();
	public function getList(): array;
	public function getAll(int $start, int $items_per_page, string $sort): array;
	public function getTotalCount(): int;
}
