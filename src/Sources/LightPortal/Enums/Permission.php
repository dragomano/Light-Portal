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

use Bugo\Compat\Db;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Traits\HasValues;
use Bugo\LightPortal\Utils\CacheInterface;

use function array_column;
use function array_filter;
use function in_array;
use function is_int;

enum Permission: int
{
	use HasValues;

	case ADMIN = 0;
	case GUEST = 1;
	case MEMBER = 2;
	case ALL = 3;
	case MOD = 4;
	case OWNER = 5;

	public static function canViewItem(self|int $permission, int $userId = 0): bool
	{
		$permission = is_int($permission) ? self::tryFrom($permission) : $permission;

		return match ($permission) {
			self::ADMIN  => User::$me->is_admin,
			self::GUEST  => User::$me->is_guest,
			self::MEMBER => User::$me->id > 0,
			self::ALL    => true,
			self::MOD    => self::isAdminOrModerator(),
			self::OWNER  => User::$me->id === $userId,
			default      => false,
		};
	}

	public static function all(): array
	{
		return match (true) {
			User::$me->is_admin => array_filter(self::values(), fn($value) => $value !== self::OWNER->value),
			User::$me->is_guest => [self::GUEST->value, self::ALL->value],
			self::isModerator() => [self::MEMBER->value, self::ALL->value, self::MOD->value],
			User::$me->id > 0   => [self::MEMBER->value, self::ALL->value],
			default             => [self::ALL->value],
		};
	}

	public static function isAdminOrModerator(): bool
	{
		return User::$me->is_admin || self::isModerator();
	}

	public static function isModerator(): bool
	{
		return in_array(User::$me->id, self::getBoardModerators()) || self::isGroupMember(2);
	}

	public static function isGroupMember(int $groupId): bool
	{
		return in_array($groupId, User::$me->groups);
	}

	private static function getBoardModerators(): array
	{
		$cache = app(CacheInterface::class);

		if (($moderators = $cache->get('board_moderators')) === null) {
			$result = Db::$db->query('', /** @lang text */ '
				SELECT id_member
				FROM {db_prefix}moderators',
			);

			$items = Db::$db->fetch_all($result);

			Db::$db->free_result($result);

			$moderators = array_column($items, 'id_member');

			$cache->put('board_moderators', $moderators);
		}

		return $moderators;
	}
}
