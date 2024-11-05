<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Args;

class ArticlesArgs
{
	public function __construct(
		public array &$columns,
		public array &$tables,
		public array &$params,
		public array &$wheres,
		public array &$orders
	) {}
}
