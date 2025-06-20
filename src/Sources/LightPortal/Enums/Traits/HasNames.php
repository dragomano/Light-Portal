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

namespace Bugo\LightPortal\Enums\Traits;

use function array_map;
use function strtolower;

trait HasNames
{
	public function name(): string
	{
		return strtolower($this->name);
	}

	public static function names(): array
	{
		return array_map(fn($item) => $item->name(), self::cases());
	}
}
