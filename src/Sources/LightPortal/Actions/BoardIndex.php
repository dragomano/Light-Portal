<?php declare(strict_types=1);

/**
 * BoardIndex.php
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

use Bugo\LightPortal\Helper;

final class BoardIndex
{
	use Helper;

	public function show(): void
	{
		$this->require('BoardIndex');

		BoardIndex();
	}
}
