<?php declare(strict_types=1);

/**
 * Placement.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Enums\Traits\HasNames;

enum Placement
{
	use HasNames;

	case HEADER;
	case TOP;
	case LEFT;
	case RIGHT;
	case BOTTOM;
	case FOOTER;
}
