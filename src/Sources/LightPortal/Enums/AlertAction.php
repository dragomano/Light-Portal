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

enum AlertAction: string
{
	use HasValues;

	case PAGE_COMMENT_MENTION = 'page_comment_mention';
	case PAGE_COMMENT_REPLY   = 'page_comment_reply';
	case PAGE_COMMENT         = 'page_comment';
	case PAGE_UNAPPROVED      = 'page_unapproved';
}
