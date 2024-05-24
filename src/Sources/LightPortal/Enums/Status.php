<?php declare(strict_types=1);

/**
 * Status.php
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

use Bugo\LightPortal\Enums\Traits\HasValues;

enum Status: int
{
	use HasValues;

	case INACTIVE = 0;
	case ACTIVE = 1;
	case UNAPPROVED = 2;
	case INTERNAL = 3;
}
