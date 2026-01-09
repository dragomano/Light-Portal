<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.11.25
 */

namespace LightPortal\Plugins\AdsBlock;

use Bugo\Compat\Lang;
use LightPortal\Enums\Traits\HasNames;

enum Placement
{
	use HasNames;

	case AFTER_EVERY_FIRST_POST;
	case AFTER_EVERY_LAST_POST;
	case AFTER_FIRST_POST;
	case AFTER_LAST_POST;
	case BEFORE_EVERY_FIRST_POST;
	case BEFORE_EVERY_LAST_POST;
	case BEFORE_FIRST_POST;
	case BEFORE_LAST_POST;
	case BOARD_BOTTOM;
	case BOARD_TOP;
	case PAGE_BOTTOM;
	case PAGE_TOP;
	case TOPIC_BOTTOM;
	case TOPIC_TOP;

	public static function all(): array
	{
		return array_combine(self::names(), Lang::$txt['lp_ads_block']['placement_set']);
	}
}
