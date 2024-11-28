<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * * @link https://custom.simplemachines.org/index.php?mod=4244
 * * @author Bugo <bugo@dragomano.ru>
 * * @copyright 2020-2024 Bugo
 * * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * *
 * * @category plugin
 * * @version 28.11.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\Traits\HasNamesTrait;

use function array_combine;

enum Placement
{
	use HasNamesTrait;

	case BOARD_TOP;
	case BOARD_BOTTOM;
	case TOPIC_TOP;
	case TOPIC_BOTTOM;
	case BEFORE_FIRST_POST;
	case BEFORE_EVERY_FIRST_POST;
	case BEFORE_EVERY_LAST_POST;
	case BEFORE_LAST_POST;
	case AFTER_FIRST_POST;
	case AFTER_EVERY_FIRST_POST;
	case AFTER_EVERY_LAST_POST;
	case AFTER_LAST_POST;
	case PAGE_TOP;
	case PAGE_BOTTOM;

	public static function all(): array
	{
		return array_combine(self::names(), Lang::$txt['lp_ads_block']['placement_set']);
	}
}
