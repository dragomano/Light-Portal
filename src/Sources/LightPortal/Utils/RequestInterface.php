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

interface RequestInterface extends GlobalArrayInterface
{
	public function is(string $action, string $type = 'action'): bool;

	public function isNot(string $action, string $type = 'action'): bool;

	public function sa(string $action): bool;

	public function json(?string $key = null, mixed $default = null): mixed;

	public function url(): string;
}
