<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Enums\Traits\HasNames;

use const LP_BASE_URL;

enum PortalSubAction
{
	use HasNames;

	case CATEGORIES;
	case TAGS;
	case PROMOTE;

	public function url(): string
	{
		return LP_BASE_URL . ';sa=' . $this->name();
	}
}
