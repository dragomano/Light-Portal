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

use LightPortal\Enums\Traits\HasValues;

enum Placement: string
{
	use HasValues;

	case HEADER = 'header';
	case TOP    = 'top';
	case LEFT   = 'left';
	case RIGHT  = 'right';
	case BOTTOM = 'bottom';
	case FOOTER = 'footer';

	public static function all(): array
	{
		return array_combine(self::values(), __('lp_block_placement_set'));
	}
}
