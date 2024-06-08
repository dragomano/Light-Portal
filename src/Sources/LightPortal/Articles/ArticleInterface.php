<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Articles;

if (! defined('SMF'))
	die('No direct access...');

interface ArticleInterface
{
	public function init(): void;

	public function getData(int $start, int $limit): array;

	public function getTotalCount(): int;
}
