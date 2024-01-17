<?php declare(strict_types=1);

/**
 * Theme.php
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

use stdClass;

final class Theme
{
	public static stdClass $current;

	public function __construct()
	{
		self::$current = new stdClass();

		if (! isset($GLOBALS['settings']))
			$GLOBALS['settings'] = [];

		self::$current->settings = &$GLOBALS['settings'];

		if (! isset($GLOBALS['options']))
			$GLOBALS['options'] = [];

		self::$current->options  = &$GLOBALS['options'];
	}
}
