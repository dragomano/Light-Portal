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

namespace Bugo\LightPortal\Enums;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\Traits\HasNamesTrait;

use function array_combine;

enum Placement
{
	use HasNamesTrait;

	case HEADER;
	case TOP;
	case LEFT;
	case RIGHT;
	case BOTTOM;
	case FOOTER;

	public static function all(): array
	{
		return array_combine(self::names(), Lang::$txt['lp_block_placement_set']);
	}
}
