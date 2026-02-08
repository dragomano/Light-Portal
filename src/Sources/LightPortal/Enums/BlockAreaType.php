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

use LightPortal\Enums\Traits\HasNames;

enum BlockAreaType
{
	use HasNames;

	case CUSTOM_ACTION;
	case CUSTOM_ACTION_EXCEPT;
	case PAGE_SLUG;
	case BOARD_ID;
	case BOARD_RANGE;
	case BOARD_SET;
	case TOPIC_ID;
	case TOPIC_RANGE;
	case TOPIC_SET;
}
