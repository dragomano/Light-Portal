<?php declare(strict_types=1);

/**
 * Config.php
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

use function memoryReturnBytes;
use function updateSettings;

if (! defined('SMF'))
	die('No direct access...');

final class Config
{
	public static ?array $modSettings = null;

	public static string $scripturl;

	public static string $boardurl;

	public static string $boarddir;

	public static string $sourcedir;

	public static string $cachedir;

	public static string $db_type;

	public static string $db_prefix;

	public static string $language;

	public static string $mbname;

	public static int $cache_enable;

	public static bool $db_show_debug;

	private array $vars = [
		'modSettings'   => [],
		'scripturl'     => '',
		'boardurl'      => '',
		'boarddir'      => '',
		'sourcedir'     => '',
		'cachedir'      => '',
		'db_type'       => '',
		'db_prefix'     => '',
		'language'      => '',
		'mbname'        => '',
		'cache_enable'  => 0,
		'db_show_debug' => false,
	];

	public function __construct()
	{
		foreach ($this->vars as $key => $value) {
			if (! isset($GLOBALS[$key])) {
				$GLOBALS[$key] = $value;
			}

			self::${$key} = &$GLOBALS[$key];
		}
	}

	public static function memoryReturnBytes(string $val): int
	{
		return memoryReturnBytes($val);
	}

	public static function updateModSettings(array $settings): void
	{
		updateSettings($settings);
	}
}
