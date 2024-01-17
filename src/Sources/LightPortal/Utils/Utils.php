<?php declare(strict_types=1);

/**
 * Utils.php
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

final class Utils
{
	public static array $context;

	public static array $smcFunc;

	public function __construct()
	{
		self::$context = &$GLOBALS['context'];

		self::$smcFunc = &$GLOBALS['smcFunc'];
	}
}
