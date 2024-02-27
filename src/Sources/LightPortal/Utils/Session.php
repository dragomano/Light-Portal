<?php declare(strict_types=1);

/**
 * Session.php
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

final class Session extends GlobalArray
{
	public function __construct()
	{
		$this->storage = &$_SESSION;
	}

	public function free(string $key): void
	{
		unset($this->storage[$key]);
	}
}
