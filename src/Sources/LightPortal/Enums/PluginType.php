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

use Bugo\Compat\Lang;
use LightPortal\Enums\Traits\HasNames;

enum PluginType
{
	use HasNames;

	case ARTICLE;
	case BLOCK;
	case BLOCK_OPTIONS;
	case COMMENT;
	case EDITOR;
	case FRONTPAGE;
	case GAMES;
	case ICONS;
	case IMPEX;
	case OTHER;
	case PAGE_OPTIONS;
	case PARSER;
	case SEO;
	case SSI;

	public static function all(): array
	{
		return Lang::$txt['lp_plugins_types'];
	}

	public static function colors(): array
	{
		return [
			self::ARTICLE->name()       => '#ef564f',
			self::BLOCK->name()         => '#667d99',
			self::BLOCK_OPTIONS->name() => '#ac7bd6',
			self::COMMENT->name()       => '#9354ca',
			self::EDITOR->name()        => '#48bf83',
			self::FRONTPAGE->name()     => '#d68b4f',
			self::GAMES->name()         => '#ff0000',
			self::ICONS->name()         => '#2a7750',
			self::IMPEX->name()         => '#2361ad',
			self::OTHER->name()         => '#414141',
			self::PAGE_OPTIONS->name()  => '#a39d47',
			self::PARSER->name()        => '#91ae26',
			self::SEO->name()           => '#c61a12',
			self::SSI->name()           => '#5f2c8c',
		];
	}
}
