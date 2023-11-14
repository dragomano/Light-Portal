<?php declare(strict_types=1);

/**
 * File.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Utils;

final class File extends GlobalArray
{
	public function __construct()
	{
		$this->storage = &$_FILES;
	}

	public function free(string $key)
	{
		unset($this->storage[$key]);
	}
}
