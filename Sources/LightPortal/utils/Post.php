<?php

namespace Bugo\LightPortal\Utils;

/**
 * Post.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

class Post extends Request
{
	public static $obj;

	public function __construct()
	{
		static::$obj = &$_POST;
	}
}
