<?php

namespace Bugo\LightPortal\Front;

/**
 * ArticleInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

interface ArticleInterface
{
	public function getData(int $start, int $limit);
	public function getTotal();
}