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
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use IntlException;
use MessageFormatter;

if (! defined('SMF'))
	die('No direct access...');

final class Lang extends SMFLang
{
	public const FALLBACK_LANG = 'english';

	public static function getLanguageNameFromLocale(string $language): string
	{
		return $language;
	}

	/**
	 * Translates a message using the given pattern and values.
	 *
	 * @see https://github.com/dragomano/Light-Portal/wiki/Info-for-translators
	 * @see https://symfony.com/doc/6.1/translation/message_format.html
	 * @see https://intl.rmcreative.ru
	 */
	public static function getTxt(string|array $txt_key, array $args = [], string $var = 'txt'): string
	{
		return self::getFormattedText($txt_key, $args, $var);
	}

	/**
	 * Translates a message using the given pattern and values.
	 *
	 * @see https://github.com/dragomano/Light-Portal/wiki/Info-for-translators
	 * @see https://symfony.com/doc/6.1/translation/message_format.html
	 * @see https://intl.rmcreative.ru
	 */
	private static function getFormattedText(string $key, array $args = [], string $var = 'txt'): string
	{
		if (! extension_loaded('intl')) {
			ErrorHandler::log('[LP] getTxt helper: you should enable the intl extension', 'critical');

			return '';
		}

		$message = Lang::${$var}[$key] ?? $key;

		try {
			$formatter = new MessageFormatter(Lang::$txt['lang_locale'] ?? 'en_US', $message);

			return $formatter->format($args);
		} catch (IntlException $e) {
			ErrorHandler::log("[LP] getTxt helper: {$e->getMessage()} in '\${$var}[$key]'", 'critical');

			return '';
		}
	}
}
