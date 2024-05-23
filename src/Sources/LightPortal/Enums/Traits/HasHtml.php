<?php declare(strict_types=1);

/**
 * HasHtml.php
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

trait HasHtml
{
	public static function values(): array
	{
		return array_combine(
			array_map(fn($class) => $class->value, self::cases()),
			array_map(fn($class) => $class->getList(), self::cases())
		);
	}

	public static function html(string $content, string $class = ''): string
	{
		return sprintf(self::values()[$class] ?? self::values()[''], $content);
	}
}
