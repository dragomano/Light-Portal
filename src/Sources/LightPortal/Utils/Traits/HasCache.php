<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Utils\Traits;

use Bugo\LightPortal\Utils\Cache;

trait HasCache
{
	public function cache(?string $key = null): Cache
	{
		return app(Cache::class)($key);
	}
}
