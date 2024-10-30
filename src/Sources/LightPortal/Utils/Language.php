<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\{Config, Lang, User, Utils};

use function array_flip;
use function array_merge;
use function str_starts_with;

if (! defined('SMF'))
	die('No direct access...');

final class Language
{
	public const FALLBACK = 'english';

	public static function getFallbackValue(): string
	{
		return str_starts_with(SMF_VERSION, '3.0') ? 'en_US' : self::FALLBACK;
	}

	public static function getNameFromLocale(string $locale): string
	{
		if (str_starts_with(SMF_VERSION, '3.0')) {
			return array_flip(Lang::LANG_TO_LOCALE)[$locale] ?? self::FALLBACK;
		}

		return $locale;
	}

	public static function prepareList(): void
	{
		$temp = Lang::get();

		if (empty(Config::$modSettings['userLanguage'])) {
			Utils::$context['lp_languages'] = [
				Config::$language => $temp[Config::$language]
			];

			return;
		}

		Utils::$context['lp_languages'] = array_merge([
			User::$info['language'] => $temp[User::$info['language']],
			Config::$language => $temp[Config::$language],
		], $temp);
	}
}
