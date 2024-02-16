<?php declare(strict_types=1);

/**
 * LanguageNext.php (special for SMF 3.0)
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

use SMF\Lang;

if (! defined('SMF'))
	die('No direct access...');

final class LanguageNext
{
	public const FALLBACK = 'en_US';

	public static function getNameFromLocale(string $locale): string
	{
		return array_flip(Lang::LANG_TO_LOCALE)[$locale] ?? 'english';
	}
}
