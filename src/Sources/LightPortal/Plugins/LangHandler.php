<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\{Lang, User};
use Bugo\LightPortal\Utils\Language;

use function array_merge;
use function array_unique;
use function is_file;

class LangHandler
{
	private const PREFIX = 'lp_';

	public function handle(string $path, string $snakeName): void
	{
		if (isset(Lang::$txt[self::PREFIX . $snakeName]))
			return;

		$userLang  = Language::getNameFromLocale(User::$info['language']);
		$languages = array_unique([Language::FALLBACK, $userLang]);

		// @TODO This variable is still needed in some templates
		Lang::$txt[self::PREFIX . $snakeName] = array_merge(
			...array_map(function ($lang) use ($path) {
				$langFile = $path . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php';
				return is_file($langFile) ? require $langFile : [];
			}, $languages)
		);
	}
}
