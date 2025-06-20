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

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class Session extends GlobalArray
{
	public function __construct(?string $key = null)
	{
		if ($key) {
			if (! isset($_SESSION[$key])) {
				$_SESSION[$key] = [];
			}

			$this->storage = &$_SESSION[$key];
			return;
		}

		$this->storage = &$_SESSION;
	}

	public function withKey(?string $key): self
	{
		return new self($key);
	}

	public function free(string $key): void
	{
		unset($this->storage[$key]);
	}
}
