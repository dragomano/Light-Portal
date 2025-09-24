<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal;

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

if (! function_exists('app')) {
	/**
	 * @template RequestedType
	 * @param class-string<RequestedType>|string $service
	 * @return RequestedType|mixed
	 */
	function app(string $service = ''): mixed
	{
		if (empty($service)) {
			return Container::getInstance();
		}

		return Container::get($service);
	}
}

// This is the way
app(PortalApp::class);
