<?php declare(strict_types=1);

/**
 * LangNext.php (special for SMF 3.0)
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

final class LangNext extends SMFLang
{
	public const FALLBACK_LANG = 'en_US';

	public static function getLanguageNameFromLocale(string $locale): string
	{
		return array_flip(parent::LANG_TO_LOCALE)[$locale] ?? 'english';
	}

	public static function getTxt(string|array $txt_key, array $args = [], string $var = 'txt'): string
	{
		return parent::getTxt($txt_key, $args, $var);
	}
}
