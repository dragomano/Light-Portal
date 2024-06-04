<?php declare(strict_types=1);

/**
 * PrepareLanguages.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;

use function array_merge;

trait PrepareLanguages
{
	public function prepareForumLanguages(): void
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
