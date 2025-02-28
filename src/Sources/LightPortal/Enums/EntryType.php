<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Enums;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\Traits\HasNames;

use function array_combine;
use function array_filter;

enum EntryType
{
	use HasNames;

	case DEFAULT;
	case INTERNAL;
	case DRAFT;

	public static function all(): array
	{
		return array_combine(self::names(), Lang::$txt['lp_page_type_set']);
	}

	public static function withoutDrafts(): array
	{
		return array_filter(self::names(), fn($item) => $item !== self::DRAFT->name());
	}
}
