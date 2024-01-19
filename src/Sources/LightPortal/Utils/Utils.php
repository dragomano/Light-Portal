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
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use function JavaScriptEscape;
use function obExit;
use function redirectexit;
use function send_http_status;
use function shorten_subject;
use function smf_chmod;
use function smf_json_decode;
use function un_htmlspecialchars;

if (! defined('SMF'))
	die('No direct access...');

final class Utils
{
	public static array $context;

	public static array $smcFunc;

	public function __construct()
	{
		self::$context = &$GLOBALS['context'];

		self::$smcFunc = &$GLOBALS['smcFunc'];
	}

	public static function JavaScriptEscape(string $string, bool $as_json = false): string
	{
		return JavaScriptEscape($string, $as_json);
	}

	public static function obExit(?bool $header = null): void
	{
		obExit($header);
	}

	public static function redirectexit(string $url = ''): void
	{
		redirectexit($url);
	}

	public static function sendHttpStatus(int $code): void
	{
		send_http_status($code);
	}

	public static function shorten(string $text, int $length = 150): string
	{
		return shorten_subject($text, $length);
	}

	public static function makeWritable(string $file): bool
	{
		return smf_chmod($file);
	}

	public static function jsonDecode(string $json, $returnAsArray = true): ?array
	{
		return smf_json_decode($json, $returnAsArray) ?: null;
	}

	public static function htmlspecialcharsDecode(string $string): string
	{
		return un_htmlspecialchars($string);
	}
}
