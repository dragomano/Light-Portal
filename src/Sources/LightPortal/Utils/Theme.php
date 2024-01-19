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
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class Theme extends SMFTheme
{
	public static function addInlineJS(string $javascript, $defer = false): void
	{
		self::addInlineJavaScript($javascript, $defer);
	}

	public static function loadExtCSS(string $fileName, array $params = [], string $id = ''): void
	{
		self::loadCSSFile($fileName, array_merge($params, ['external' => true]), $id);
	}

	public static function loadExtJS(string $fileName, array $params = [], string $id = ''): void
	{
		self::loadJSFile($fileName, array_merge($params, ['external' => true]), $id);
	}

	public static function loadJSFile(string $fileName, array $params = [], string $id = ''): void
	{
		self::loadJavaScriptFile($fileName, $params, $id);
	}
}
