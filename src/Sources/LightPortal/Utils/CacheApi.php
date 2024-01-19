<?php declare(strict_types=1);

/**
 * CacheApi.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use function cache_get_data;
use function cache_put_data;
use function clean_cache;

if (! defined('SMF'))
	die('No direct access...');

final class CacheApi
{
	public static function get(string $key, int $ttl = 120): ?array
	{
		return cache_get_data($key, $ttl);
	}

	public static function put(string $key, mixed $value, int $ttl = 120): void
	{
		cache_put_data($key, $value, $ttl);
	}

	public static function clean(): void
	{
		clean_cache();
	}
}
