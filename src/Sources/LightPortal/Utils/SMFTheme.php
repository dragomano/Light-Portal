<?php declare(strict_types=1);

/**
 * SMFTheme.php
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
use function addJavaScriptVar;
use function addInlineCss;
use function addInlineJavaScript;
use function loadCSSFile;
use function loadJavaScriptFile;
use function loadEssentialThemeData;
use function loadTemplate;

if (! defined('SMF'))
	die('No direct access...');

class SMFTheme
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

	public static function addJavaScriptVar(string $key, $value, $escape = false): void
	{
		addJavaScriptVar($key, $value, $escape);
	}

	public static function addInlineCss(string $css): void
	{
		addInlineCss($css);
	}

	public static function addInlineJavaScript(string $javascript, $defer = false): void
	{
		addInlineJavaScript($javascript, $defer);
	}

	public static function loadCSSFile(string $fileName, array $params = [], string $id = ''): void
	{
		loadCSSFile($fileName, $params, $id);
	}

	public static function loadJavaScriptFile(string $fileName, array $params = [], string $id = ''): void
	{
		loadJavaScriptFile($fileName, $params, $id);
	}

	public static function loadEssential(): void
	{
		require_once Config::$sourcedir . DIRECTORY_SEPARATOR . 'ScheduledTasks.php';

		loadEssentialThemeData();
	}

	public static function loadTemplate(string $template): void
	{
		loadTemplate($template);
	}
}
