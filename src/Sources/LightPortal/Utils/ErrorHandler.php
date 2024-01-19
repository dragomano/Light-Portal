<?php declare(strict_types=1);

/**
 * ErrorHandler.php
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

use function fatal_error;
use function fatal_lang_error;
use function log_error;

if (! defined('SMF'))
	die('No direct access...');

final class ErrorHandler
{
	public static function fatal(string $message): void
	{
		fatal_error($message, false);
	}

	public static function fatalLang(string $error, string|bool $log = 'general', array $sprintf = [], int $status = 403): void
	{
		fatal_lang_error($error, false, null, $status);
	}

	public static function log(string $message, string $level = 'user'): void
	{
		log_error($message, $level);
	}
}
