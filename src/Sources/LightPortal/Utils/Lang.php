<?php declare(strict_types=1);

/**
 * Lang.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Utils;

final class Lang
{
	public static array $txt;

	public static array $editortxt;

	public function __construct()
	{
		if (! isset($GLOBALS['txt']))
			$GLOBALS['txt'] = [];

		self::$txt = &$GLOBALS['txt'];

		self::$editortxt = $GLOBALS['editortxt'] ?? [];
	}
}
