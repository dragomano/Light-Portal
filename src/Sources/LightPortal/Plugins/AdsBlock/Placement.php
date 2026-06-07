<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.04.26
 */

namespace LightPortal\Plugins\AdsBlock;

use LightPortal\Enums\Traits\HasValues;

enum Placement: string
{
	use HasValues;

	case AFTER_EVERY_FIRST_POST  = 'after_every_first_post';
	case AFTER_EVERY_LAST_POST   = 'after_every_last_post';
	case AFTER_FIRST_POST        = 'after_first_post';
	case AFTER_LAST_POST         = 'after_last_post';
	case BEFORE_EVERY_FIRST_POST = 'before_every_first_post';
	case BEFORE_EVERY_LAST_POST  = 'before_every_last_post';
	case BEFORE_FIRST_POST       = 'before_first_post';
	case BEFORE_LAST_POST        = 'before_last_post';
	case BOARD_BOTTOM            = 'board_bottom';
	case BOARD_TOP               = 'board_top';
	case PAGE_BOTTOM             = 'page_bottom';
	case PAGE_TOP                = 'page_top';
	case TOPIC_BOTTOM            = 'topic_bottom';
	case TOPIC_TOP               = 'topic_top';

	public static function all(): array
	{
		return array_combine(self::values(), __('lp_ads_block')['placement_set']);
	}
}
