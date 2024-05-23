<?php declare(strict_types=1);

/**
 * HasValues.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums\Traits;

trait HasValues
{
	public static function values(): array
	{
		return array_map(fn($item) => $item->value, self::cases());
	}
}
