<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Areas\ConfigArea;
use Bugo\LightPortal\Areas\CreditArea;

if (! defined('SMF'))
	die('No direct access...');

final class PortalApp
{
	public function __construct()
	{
		if (SMF === 'BACKGROUND')
			return;

		app(Integration::class)();
		app(ConfigArea::class)();
		app(CreditArea::class)();
	}
}
