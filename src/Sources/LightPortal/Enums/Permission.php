<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums;

use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Traits\HasValuesTrait;

enum Permission: int
{
	use HasValuesTrait;

	case ADMIN = 0;
	case GUEST = 1;
	case MEMBER = 2;
	case ALL = 3;
	case OWNER = 4;

	public static function canViewItem(self|int $permission, int $userId = 0): bool
	{
		$permission = is_int($permission) ? self::tryFrom($permission) : $permission;

		return match ($permission) {
			self::ADMIN  => User::$info['is_admin'],
			self::GUEST  => User::$info['is_guest'],
			self::MEMBER => User::$info['id'] > 0,
			self::ALL    => true,
			self::OWNER  => User::$info['id'] === $userId,
			default      => false,
		};
	}

	public static function all(): array
	{
		return match (true) {
			User::$info['is_admin'] => array_filter(self::values(), fn($value) => $value !== self::OWNER->value),
			User::$info['is_guest'] => [self::GUEST->value, self::ALL->value],
			User::$info['id'] > 0   => [self::MEMBER->value, self::ALL->value],
			default                 => [self::ALL->value],
		};
	}
}
