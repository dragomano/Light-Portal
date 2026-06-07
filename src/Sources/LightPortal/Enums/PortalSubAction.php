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

use const LP_BASE_URL;

enum PortalSubAction: string
{
	case CATEGORIES = 'categories';
	case TAGS       = 'tags';
	case PROMOTE    = 'promote';

	public function url(): string
	{
		return LP_BASE_URL . ';sa=' . $this->value;
	}
}
