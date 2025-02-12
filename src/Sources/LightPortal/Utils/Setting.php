<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

use function explode;
use function filter_var;

use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

if (! defined('SMF'))
	die('No direct access...');

class Setting
{
	public static function get(
		string $key,
		string $type = 'string',
		mixed $default = null,
		string $from = 'string'
	): mixed
	{
		if (! isset(Config::$modSettings[$key])) {
			return $default;
		}

		$value = Config::$modSettings[$key];

		return match ($type) {
			'bool'  => filter_var($value, FILTER_VALIDATE_BOOLEAN),
			'int'   => filter_var($value, FILTER_VALIDATE_INT),
			'float' => filter_var($value, FILTER_VALIDATE_FLOAT),
			'array' => self::transformArray($value, $from),
			default => filter_var($value),
		};
	}

	public static function isFrontpage(string $slug): bool
	{
		$chosenPage = self::get('lp_frontpage_chosen_page', 'string', '');

		if ($slug === '' || empty($chosenPage)) {
			return false;
		}

		return self::isFrontpageMode('chosen_page') && $chosenPage === $slug;
	}

	public static function isFrontpageMode(string $mode): bool
	{
		$frontpageMode = self::get('lp_frontpage_mode', 'string', '');

		return $frontpageMode === $mode;
	}

	public static function isStandaloneMode(): bool
	{
		$standaloneMode = self::get('lp_standalone_mode', 'bool', false);
		$standaloneUrl  = self::get('lp_standalone_url', 'string', '');

		return $standaloneMode && ! empty($standaloneUrl);
	}

	public static function getCommentBlock(): string
	{
		return self::get('lp_comment_block', 'string', '');
	}

	public static function showRelatedPages(): bool
	{
		return self::get('lp_show_related_pages', 'bool', true);
	}

	public static function getEnabledPlugins(): array
	{
		return self::get('lp_enabled_plugins', 'array', []);
	}

	public static function getFrontpagePages(): array
	{
		return self::get('lp_frontpage_pages', 'array', []);
	}

	public static function getFrontpageTopics(): array
	{
		return self::get('lp_frontpage_topics', 'array', []);
	}

	public static function getHeaderPanelWidth(): int
	{
		return self::get('lp_header_panel_width', 'int', 12);
	}

	public static function getFooterPanelWidth(): int
	{
		return self::get('lp_footer_panel_width', 'int', 12);
	}

	public static function getLeftPanelWidth(): array
	{
		return self::get('lp_left_panel_width', 'array', ['lg' => 3, 'xl' => 2], 'json');
	}

	public static function getRightPanelWidth(): array
	{
		return self::get('lp_right_panel_width', 'array', ['lg' => 3, 'xl' => 2], 'json');
	}

	public static function getPanelDirection(string $panel): string
	{
		$directions = self::get('lp_panel_direction', 'array', [], 'json');

		return $directions[$panel] ?? '0';
	}

	public static function isSwapLeftRight(): bool
	{
		$isRtl = ! empty(Lang::$txt['lang_rtl']);
		$swapLeftRight = self::get('lp_swap_left_right', 'bool', false);

		return $isRtl ? ! $swapLeftRight : $swapLeftRight;
	}

	public static function hideBlocksInACP(): bool
	{
		$hideBlocks = self::get('lp_hide_blocks_in_acp', 'bool', false);

		return $hideBlocks && app(Request::class)->is('admin');
	}

	public static function getDisabledActions(): array
	{
		return self::get('lp_disabled_actions', 'array', []);
	}

	protected static function transformArray(string $value, string $from): array
	{
		return $from === 'json' ? Utils::jsonDecode($value, true) : explode(',', $value);
	}
}
