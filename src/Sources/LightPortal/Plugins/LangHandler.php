<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Utils\Language;

if (! defined('LP_NAME'))
	die('No direct access...');

class LangHandler
{
	private const PREFIX = 'lp_';

	public function handle(string $path, string $snakeName): void
	{
		if (isset(Lang::$txt[self::PREFIX . $snakeName]))
			return;

		if (! isset(User::$me)) {
			User::load();
		}

		$userLang  = Language::getNameFromLocale(User::$me->language);
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
