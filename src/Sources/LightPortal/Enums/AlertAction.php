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

use Bugo\LightPortal\Enums\Traits\HasNamesTrait;

enum AlertAction
{
	use HasNamesTrait;

	case PAGE_COMMENT;
	case PAGE_COMMENT_REPLY;
	case PAGE_UNAPPROVED;
}
