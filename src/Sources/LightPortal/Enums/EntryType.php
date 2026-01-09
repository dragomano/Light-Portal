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

use Bugo\Compat\Lang;
use LightPortal\Enums\Traits\HasNames;

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
