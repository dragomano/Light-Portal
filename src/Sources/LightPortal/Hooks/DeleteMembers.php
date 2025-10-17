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

namespace Bugo\LightPortal\Hooks;

use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasPortalSql;

if (! defined('SMF'))
	die('No direct access...');

class DeleteMembers
{
	use HasCache;
	use HasPortalSql;

	public function __invoke(array $users): void
	{
		if (empty($users))
			return;

		$delete = $this->getPortalSql()->delete('lp_comments');
		$delete->where->in('author_id', $users);
		$this->getPortalSql()->execute($delete);

		$delete = $this->getPortalSql()->delete('user_alerts');
		$delete->where->in('id_member', $users)->or->in('id_member_started', $users);
		$this->getPortalSql()->execute($delete);

		$this->cache()->flush();
	}
}
