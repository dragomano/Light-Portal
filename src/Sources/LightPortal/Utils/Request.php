<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

class Request extends GlobalArray implements RequestInterface
{
	public function __construct()
	{
		$this->storage = &$_REQUEST;
	}

	public function is(string $action, string $type = 'action'): bool
	{
		return $this->has($type) && $this->storage[$type] === $action;
	}

	public function isNot(string $action, string $type = 'action'): bool
	{
		return empty($this->is($action, $type));
	}

	public function sa(string $action): bool
	{
		return $this->is($action, 'sa');
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
