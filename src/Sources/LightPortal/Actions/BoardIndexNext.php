<?php declare(strict_types=1);

/**
 * BoardIndexNext.php (special for SMF 3.0)
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

use SMF\Actions\BoardIndex;

if (! defined('SMF'))
	die('No direct access...');

final class BoardIndexNext implements ActionInterface
{
	public function show(): void
	{
		BoardIndex::call();
	}
}
