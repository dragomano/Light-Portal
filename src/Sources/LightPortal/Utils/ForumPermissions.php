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

namespace LightPortal\Utils;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Laminas\Db\Sql\Predicate\Expression;

if (! defined('SMF'))
	die('No direct access...');

class ForumPermissions
{
	public static function canSeeBoard(string $alias = 't'): Expression
	{
		$prefix = Config::$db_prefix;

		return new Expression(
			"EXISTS (SELECT bpv.id_board FROM {$prefix}board_permissions_view AS bpv " .
			"WHERE bpv.id_group IN (?) AND bpv.deny = 0 AND bpv.id_board = $alias.id_board)",
			[implode(',', User::$me->groups)]
		);
	}

	public static function shouldApplyBoardPermissionCheck(): bool
	{
		$canSeeAllBoards = in_array(1, User::$me->groups)
			|| array_intersect(User::$me->groups, explode(',', Config::$modSettings['board_manager_groups'] ?? '')) !== [];

		return ! $canSeeAllBoards;
	}
}
