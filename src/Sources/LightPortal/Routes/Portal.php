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

namespace Bugo\LightPortal\Routes;

use Bugo\Compat\Routable;
use Bugo\LightPortal\Utils\Cache;

use function array_search;
use function array_shift;
use function count;
use function in_array;

class Portal implements Routable
{
	public static function getDataFromCache(string $type = 'categories'): array
	{
		return (new Cache())->get('lp_sef_' . $type) ?? [];
	}

	public static function getCachedName(string $id, string $type = 'categories'): string
	{
		$data = self::getDataFromCache($type);

		if (empty($data))
			return $id;

		return $data[(int) $id] ?? $id;
	}

	public static function getEntryId(string $id, string $type = 'categories'): string
	{
		$data = self::getDataFromCache($type);

		if (empty($data))
			return $id;

		return (string) array_search($id, $data);
	}

	public static function buildRoute(array $params): array
	{
		$route[] = $params['action'];

		unset($params['action']);

		if (isset($params['sa'])) {
			$route[] = $params['sa'];

			if (isset($params['id'])) {
				$route[] = self::getCachedName($params['id'], $params['sa']);

				unset($params['id']);
			}

			unset($params['sa']);

			if (in_array('promote', $route) && isset($params['t'])) {
				$route[] = $params['t'];

				unset($params['t']);
			}
		}

		if (isset($params['start'])) {
			if ($params['start'] > 0) {
				$route[] = $params['start'];
			} else {
				$route = [];
			}

			unset($params['start']);
		}

		return ['route' => $route, 'params' => $params];
	}

	public static function parseRoute(array $route, array $params = []): array
	{
		$params['action'] = array_shift($route);

		if (! empty($route) && count($route) > 1) {
			$params['sa'] = array_shift($route);

			if (! empty($route)) {
				$id = array_shift($route);
				$params['id'] = self::getEntryId($id, $params['sa']);
			}

			if ($params['sa'] === 'promote') {
				$params['t'] = $params['id'];

				unset($params['id']);
			}
		}

		if (! empty($route)) {
			$params['start'] = array_shift($route);
		}

		return $params;
	}
}
