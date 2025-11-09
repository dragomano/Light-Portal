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

namespace LightPortal\Routes;

use Bugo\Compat\Routable;

class Forum implements Routable
{
	public static function buildRoute(array $params): array
	{
		$route[] = $params['action'];

		unset($params['action']);

		return ['route' => $route, 'params' => $params];
	}

	public static function parseRoute(array $route, array $params = []): array
	{
		$params['action'] = array_shift($route);

		return $params;
	}
}
