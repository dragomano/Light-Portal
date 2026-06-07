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

use LightPortal\Enums\Traits\HasValues;

enum EntryType: string
{
	use HasValues;

	case DEFAULT  = 'default';
	case INTERNAL = 'internal';
	case DRAFT    = 'draft';

	public static function all(): array
	{
		return array_combine(self::values(), __('lp_page_type_set'));
	}

	public static function withoutDrafts(): array
	{
		return array_filter(self::values(), fn($item) => $item !== self::DRAFT->value);
	}
}
