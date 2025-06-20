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

namespace Bugo\LightPortal\Routes;

use Bugo\Compat\Routable;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Utils\Response;

use function array_shift;

use const LP_PAGE_PARAM;

class Page implements Routable
{
	public static function buildRoute(array $params): array
	{
		$route = [];

		if (isset($params[LP_PAGE_PARAM])) {
			$route[] = Action::PAGES->value;
			$route[] = $params[LP_PAGE_PARAM];

			unset($params[LP_PAGE_PARAM]);
		}

		return ['route' => $route, 'params' => $params];
	}

	public static function parseRoute(array $route, array $params = []): array
	{
		array_shift($route);

		// We need to redirect from "/pages" to "/"
		if (empty($route)) {
			(new Response())->redirect();
		}

		$params[LP_PAGE_PARAM] = array_shift($route);

		return $params;
	}
}
