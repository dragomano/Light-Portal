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
 * @version 2.6
 */

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\BoardIndex as Index;

if (! defined('SMF'))
	die('No direct access...');

final class BoardIndex implements ActionInterface
{
	public function show(): void
	{
		Index::call();
	}
}
