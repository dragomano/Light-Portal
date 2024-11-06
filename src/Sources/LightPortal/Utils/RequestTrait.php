<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Utils;

trait RequestTrait
{
	public function request(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Request())->get($key) ?? $default) : new Request();
	}

	public function post(?string $key = null, mixed $default = null): mixed
	{
		return $key ? ((new Post())->get($key) ?? $default) : new Post();
	}

	public function files(?string $key = null): mixed
	{
		return $key ? (new File())->get($key) : new File();
	}
}
