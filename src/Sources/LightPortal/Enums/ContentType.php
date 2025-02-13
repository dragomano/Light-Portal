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
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Traits\HasNames;

use function array_keys;
use function array_reduce;
use function array_slice;

enum ContentType
{
	use HasNames;

	case BBC;
	case HTML;
	case PHP;

	public static function all(): array
	{
		$types = [
			self::BBC->name()  => Lang::$txt['lp_bbc']['title'],
			self::HTML->name() => Lang::$txt['lp_html']['title'],
			self::PHP->name()  => Lang::$txt['lp_php']['title'],
		];

		return User::$info['is_admin'] ? $types : array_slice($types, 0, 2);
	}

	public static function icon(string $type): string
	{
		return match($type) {
			self::BBC->name()  => 'fab fa-bimobject',
			self::HTML->name() => 'fab fa-html5',
			self::PHP->name()  => 'fab fa-php',
			default            => '',
		};
	}

	public static function default(): array
	{
		$types = self::all();

		return array_reduce(array_keys($types), function($carry, $type) use ($types) {
			$carry[$type] = [
				'icon' => self::icon($type),
			];
			return $carry;
		}, []);
	}
}
