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

use Bugo\Compat\User;
use LightPortal\Enums\Traits\HasValues;

enum ContentType: string
{
	use HasValues;

	case BBC  = 'bbc';
	case HTML = 'html';
	case PHP  = 'php';

	public static function all(): array
	{
		$types = [
			self::BBC->value  => __('lp_bbc')['title'],
			self::HTML->value => __('lp_html')['title'],
			self::PHP->value  => __('lp_php')['title'],
		];

		return User::$me->is_admin ? $types : array_slice($types, 0, 2);
	}

	public static function icon(string $type): string
	{
		return match($type) {
			self::BBC->value  => 'fab fa-bimobject',
			self::HTML->value => 'fab fa-html5',
			self::PHP->value  => 'fab fa-php',
			default           => '',
		};
	}

	public static function default(): array
	{
		$types = self::all();

		return array_reduce(array_keys($types), function($carry, $type) {
			$carry[$type] = [
				'icon' => self::icon($type),
			];

			return $carry;
		}, []);
	}
}
