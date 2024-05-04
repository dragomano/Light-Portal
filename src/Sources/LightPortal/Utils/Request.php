<?php declare(strict_types=1);

/**
 * Request.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

final class Request extends GlobalArray
{
	public function __construct()
	{
		$this->storage = &$_REQUEST;
	}

	public function is(string $action): bool
	{
		return $this->has('action') && $this->storage['action'] === $action;
	}

	public function isNot(string $action): bool
	{
		return empty($this->is($action));
	}

	public function json(?string $key = null, mixed $default = null): mixed
	{
		$input = file_get_contents('php://input');
		$data = json_decode($input, true) ?? [];

		return $key ? ($data[$key] ?? $default) : $data;
	}

	public function url(): string
	{
		return $_SERVER['REQUEST_URL'] ?? '';
	}
}
