<?php declare(strict_types = 1);

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

use Bugo\Compat\{Config, Lang, Utils};

use function explode;

if (! defined('SMF'))
	die('No direct access...');

class Setting
{
	use RequestTrait;

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

	public static function getEnabledPlugins(): array
	{
		return empty(Config::$modSettings['lp_enabled_plugins'])
			? []
			: explode(',', (string) Config::$modSettings['lp_enabled_plugins']);
	}

	public static function getFrontpagePages(): array
	{
		return empty(Config::$modSettings['lp_frontpage_pages'])
			? []
			: explode(',', (string) Config::$modSettings['lp_frontpage_pages']);
	}

	public static function getFrontpageTopics(): array
	{
		return empty(Config::$modSettings['lp_frontpage_topics'])
			? []
			: explode(',', (string) Config::$modSettings['lp_frontpage_topics']);
	}

	public static function getHeaderPanelWidth(): int
	{
		return empty(Config::$modSettings['lp_header_panel_width'])
			? 12
			: (int) Config::$modSettings['lp_header_panel_width'];
	}

	public static function getFooterPanelWidth(): int
	{
		return empty(Config::$modSettings['lp_footer_panel_width'])
			? 12
			: (int) Config::$modSettings['lp_footer_panel_width'];
	}

	public static function getLeftPanelWidth(): array
	{
		return empty(Config::$modSettings['lp_left_panel_width'])
			? ['lg' => 3, 'xl' => 2]
			: Utils::jsonDecode(Config::$modSettings['lp_left_panel_width'], true);
	}

	public static function getRightPanelWidth(): array
	{
		return empty(Config::$modSettings['lp_right_panel_width'])
			? ['lg' => 3, 'xl' => 2]
			: Utils::jsonDecode(Config::$modSettings['lp_right_panel_width'], true);
	}

	public static function getPanelDirection(): array
	{
		return Utils::jsonDecode(Config::$modSettings['lp_panel_direction'] ?? '', true);
	}

	public static function isSwapLeftRight(): bool
	{
		return empty(Lang::$txt['lang_rtl'])
			? ! empty(Config::$modSettings['lp_swap_left_right'])
			: empty(Config::$modSettings['lp_swap_left_right']);
	}

	public static function hideBlocksInACP(): bool
	{
		return ! empty(Config::$modSettings['lp_hide_blocks_in_acp']) && $this->request()->is('admin');
	}
}
