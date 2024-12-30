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

namespace Bugo\LightPortal\Utils;

trait RequestTrait
{
	public function request(?string $key = null, mixed $default = null): mixed
	{
		return $key ? (app('request')->get($key) ?? $default) : app('request');
	}

	public function post(?string $key = null, mixed $default = null): mixed
	{
		return $key ? (app('post')->get($key) ?? $default) : app('post');
	}

	public function files(?string $key = null): mixed
	{
		return $key ? app('file')->get($key) : app('file');
	}
}
