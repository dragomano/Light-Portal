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

namespace Bugo\LightPortal\Enums\Traits;

use function array_combine;
use function array_key_first;
use function array_map;
use function sprintf;

trait HasHtml
{
	public static function values(): array
	{
		return array_combine(
			array_map(fn($class) => $class->value, self::cases()),
			array_map(fn($class) => $class->getList(), self::cases())
		);
	}

	public static function first(): string
	{
		return array_key_first(self::values());
	}

	public static function html(string $content, string $class = ''): string
	{
		return sprintf(self::values()[$class] ?? self::values()[''], $content);
	}
}
