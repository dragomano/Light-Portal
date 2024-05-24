<?php declare(strict_types=1);

/**
 * Tab.php
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

enum Tab
{
	use HasNames;

	case CONTENT;
	case ACCESS_PLACEMENT;
	case APPEARANCE;
	case SEO;
	case TUNING;
}
