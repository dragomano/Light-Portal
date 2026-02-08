<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal;

use LightPortal\Hooks\Integration;

if (! defined('SMF'))
	die('No direct access...');

final class PortalApp
{
	public function __construct()
	{
		if (SMF === 'BACKGROUND')
			return;

		app(Integration::class)();
	}
}
