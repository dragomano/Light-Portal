<?php declare(strict_types=1);

/**
 * Notify.php
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

use function getNotifyPrefs;

if (! defined('SMF'))
	die('No direct access...');

final class Notify
{
	public static function getNotifyPrefs(int|array $members, string|array $prefs = '', bool $process_default = false): array
	{
		require_once Config::$sourcedir . DIRECTORY_SEPARATOR . 'Subs-Notify.php';

		return getNotifyPrefs($members, $prefs, $process_default);
	}
}
