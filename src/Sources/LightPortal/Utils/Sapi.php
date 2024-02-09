<?php declare(strict_types=1);

/**
 * Sapi.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use function memoryReturnBytes;
use function sm_temp_dir;

if (! defined('SMF'))
	die('No direct access...');

final class Sapi
{
	public static function memoryReturnBytes(string $val): int
	{
		return memoryReturnBytes($val);
	}

	public static function getTempDir(): string
	{
		require_once Config::$sourcedir . DIRECTORY_SEPARATOR . 'Subs-Admin.php';

		return sm_temp_dir();
	}

	public static function setTimeLimit(int $limit = 600): void
	{
		@set_time_limit($limit);
	}
}
