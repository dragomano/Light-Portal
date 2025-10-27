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
use LightPortal\Enums\PortalSubAction;
use LightPortal\Utils\CacheInterface;
use Throwable;

use function LightPortal\app;

use const LP_ACTION;

class Portal implements Routable
{
	public static function getDataFromCache(string $type = 'categories'): array
	{
		try {
			return app(CacheInterface::class)->get('lp_sef_' . $type) ?: [];
		} catch (Throwable) {
			return [];
		}
	}

	public static function getCachedName(string $id, string $type = 'categories'): string
	{
		return self::getDataFromCache($type)[(int) $id] ?? $id;
	}

	public static function getEntryId(string $id, string $type = 'categories'): string
	{
		return (string) array_search($id, self::getDataFromCache($type), true) ?: $id;
	}

	public static function buildRoute(array $params): array
	{
		$route = [];

		if (count($params) > 1) {
			$route[] = $params['action'];
		}

		unset($params['action']);

		if (isset($params['sa'])) {
			$route[] = $params['sa'];

			if ($params['sa'] === PortalSubAction::PROMOTE->name() && isset($params['t'])) {
				$route[] = $params['t'];

				unset($params['t'], $params['start']);
			} elseif (isset($params['id'])) {
				$route[] = self::getCachedName($params['id'], $params['sa']);

				unset($params['id']);
			}

			unset($params['sa']);
		}

		if (isset($params['start'])) {
			$route[] = $params['start'];
		}

		unset($params['start']);

		if ($route === [LP_ACTION, '0']) {
			$route = [];
		}

		return ['route' => $route, 'params' => $params];
	}

	public static function parseRoute(array $route, array $params = []): array
	{
		if (empty($route)) {
			$params['action'] = LP_ACTION;

			return $params;
		}

		$params['action'] = array_shift($route);

		if (count($route) === 1 && is_numeric($route[0])) {
			$params['start'] = array_shift($route);

			return $params;
		}

		if (! empty($route)) {
			$params['sa'] = array_shift($route);

			if (! empty($route)) {
				if ($params['sa'] === PortalSubAction::PROMOTE->name()) {
					$params['t'] = array_shift($route);
				} elseif (in_array($params['sa'], [PortalSubAction::CATEGORIES->name(), PortalSubAction::TAGS->name()])) {
					if (is_numeric($route[0])) {
						$params['start'] = array_shift($route);
					} else {
						$params['id'] = self::getEntryId(array_shift($route), $params['sa']);

						if (! empty($route) && is_numeric($route[0])) {
							$params['start'] = array_shift($route);
						}
					}
				}
			}
		}

		return $params;
	}
}
