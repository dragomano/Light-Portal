<?php declare(strict_types=1);

/**
 * Post.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal\Utils;

final class Post extends GlobalArray
{
	public function __construct()
	{
		$this->storage = &$_POST;
	}
}
