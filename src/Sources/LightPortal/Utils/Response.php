<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Utils;

class Response
{
	public function json(mixed $data, int $flags = 0): false|string
	{
		header('Content-Type: application/json; charset=utf-8');

		return json_encode($data, $flags);
	}

	public function exit(mixed $data, int $flags = 0): never
	{
		exit($this->json($data, $flags));
	}

	public function redirect(string $url = ''): void
	{
		Utils::redirectexit($url);
	}
}
