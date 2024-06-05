<?php declare(strict_types=1);

/**
 * DeleteMembers.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Db;
use Bugo\LightPortal\Utils\CacheTrait;

if (! defined('SMF'))
	die('No direct access...');

class DeleteMembers
{
	use CacheTrait;

	public function __invoke(array $users): void
	{
		if (empty($users))
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE author_id IN ({array_int:users})',
			[
				'users' => $users,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}user_alerts
			WHERE id_member IN ({array_int:users})
				OR id_member_started IN ({array_int:users})',
			[
				'users' => $users,
			]
		);

		$this->cache()->flush();
	}
}
