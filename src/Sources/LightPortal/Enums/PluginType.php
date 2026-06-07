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

namespace LightPortal\Enums;

enum PluginType: string
{
	case ARTICLE       = 'article';
	case BLOCK         = 'block';
	case BLOCK_OPTIONS = 'block_options';
	case COMMENT       = 'comment';
	case EDITOR        = 'editor';
	case FRONTPAGE     = 'frontpage';
	case GAMES         = 'games';
	case ICONS         = 'icons';
	case IMPEX         = 'impex';
	case OTHER         = 'other';
	case PAGE_OPTIONS  = 'page_options';
	case PARSER        = 'parser';
	case SEO           = 'seo';
	case SSI           = 'ssi';

	public function color(): string
	{
		return match ($this) {
			self::ARTICLE       => '#ef564f',
			self::BLOCK         => '#667d99',
			self::BLOCK_OPTIONS => '#ac7bd6',
			self::COMMENT       => '#9354ca',
			self::EDITOR        => '#48bf83',
			self::FRONTPAGE     => '#d68b4f',
			self::GAMES         => '#ff0000',
			self::ICONS         => '#2a7750',
			self::IMPEX         => '#2361ad',
			self::OTHER         => '#414141',
			self::PAGE_OPTIONS  => '#a39d47',
			self::PARSER        => '#91ae26',
			self::SEO           => '#c61a12',
			self::SSI           => '#5f2c8c',
		};
	}

	public static function colors(): array
	{
		return array_column(
			array_map(fn(self $type) => [$type->value, $type->color()], self::cases()),
			1,
			0
		);
	}

	public static function all(): array
	{
		return __('lp_plugins_types');
	}
}
