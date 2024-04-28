<?php declare(strict_types=1);

/**
 * Icon.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

final class Icon
{
	public static function get(string $name, string $title = '', string $prefix = ''): string
	{
		$icon = self::all()[$name];

		if (empty($title)) {
			return $icon;
		}

		return str_replace(' class="', ' title="' . $title . '" class="' . $prefix, $icon);
	}

	public static function all(): array
	{
		return (new EntityManager())('icon');
	}
}
