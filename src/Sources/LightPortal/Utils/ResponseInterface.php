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

namespace LightPortal\Utils;

interface ResponseInterface
{
	public function json(mixed $data, int $flags = 0): false|string;

	public function exit(mixed $data, int $flags = 0): never;

	public function redirect(string $url = ''): void;
}
