<?php declare(strict_types=1);

/**
 * ArticleInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Front;

if (! defined('SMF'))
	die('No direct access...');

interface ArticleInterface
{
	public function init();

	public function getData(int $start, int $limit): array;

	public function getTotalCount(): int;
}