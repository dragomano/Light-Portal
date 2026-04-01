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

enum BlockAreaType: string
{
	case CUSTOM_ACTION        = 'custom_action';
	case CUSTOM_ACTION_EXCEPT = 'custom_action_except';
	case PAGE_SLUG            = 'page_slug';
	case BOARD_ID             = 'board_id';
	case BOARD_RANGE          = 'board_range';
	case BOARD_SET            = 'board_set';
	case TOPIC_ID             = 'topic_id';
	case TOPIC_RANGE          = 'topic_range';
	case TOPIC_SET            = 'topic_set';
}
