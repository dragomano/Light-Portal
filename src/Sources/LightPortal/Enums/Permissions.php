<?php declare(strict_types=1);

/**
 * Permissions.php
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

enum Permissions: int
{
	case ADMIN = 0;
	case GUEST = 1;
	case MEMBER = 2;
	case ALL = 3;
	case OWNER = 4;
}
