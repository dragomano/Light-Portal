<?php

namespace Bugo\LightPortal\Utils;

/**
 * Session.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
 */

class Session extends Arr
{
	public static $obj;

	public function __construct()
	{
		static::$obj = $_SESSION;
	}
}
