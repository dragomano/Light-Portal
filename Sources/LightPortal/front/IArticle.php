<?php

namespace Bugo\LightPortal\Front;

/**
 * IArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

interface IArticle
{
	public static function show();
	public static function prepare(string $source);
	public static function getData(int $start, int $limit): array;
	public static function getTotal(): int;
}