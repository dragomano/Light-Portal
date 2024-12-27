<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Enums\Traits\HasNamesTrait;

enum Tab
{
	use HasNamesTrait;

	case CONTENT;
	case ACCESS_PLACEMENT;
	case APPEARANCE;
	case SEO;
	case TUNING;
}
