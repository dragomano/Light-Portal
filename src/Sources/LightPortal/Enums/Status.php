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

use Bugo\LightPortal\Enums\Traits\HasValuesTrait;

enum Status: int
{
	use HasValuesTrait;

	case INACTIVE = 0;
	case ACTIVE = 1;
	case UNAPPROVED = 2;
}
