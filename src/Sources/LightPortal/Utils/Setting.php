<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Config;

if (! defined('SMF'))
	die('No direct access...');

class Setting
{
	public static function isFrontpage(string $slug): bool
	{
		if ($slug === '' || empty(Config::$modSettings['lp_frontpage_chosen_page']))
			return false;

		return self::isFrontpageMode('chosen_page') && Config::$modSettings['lp_frontpage_chosen_page'] === $slug;
	}

	public static function isFrontpageMode(string $mode): bool
	{
		if (empty(Config::$modSettings['lp_frontpage_mode']))
			return false;

		return Config::$modSettings['lp_frontpage_mode'] === $mode;
	}

	public static function isStandaloneMode(): bool
	{
		if (empty(Config::$modSettings['lp_standalone_mode']))
			return false;

		return ! empty(Config::$modSettings['lp_standalone_url']);
	}

	public static function getCommentBlock(): string
	{
		return Config::$modSettings['lp_comment_block'] ?? '';
	}

	public static function showRelatedPages(): bool
	{
		return empty(Config::$modSettings['lp_show_related_pages']);
	}
}
