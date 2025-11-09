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

namespace LightPortal\Repositories;

use Laminas\Db\Sql\Where;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractIndexRepository extends AbstractRepository
{
	protected function getCommonPageWhere(): Where
	{
		$where = new Where();
		$where
			->equalTo('p.status', Status::ACTIVE->value)
			->equalTo('p.deleted_at', 0)
			->equalTo('p.entry_type', EntryType::DEFAULT->name())
			->lessThanOrEqualTo('p.created_at', time())
			->in('p.permissions', Permission::all());

		return $where;
	}
}
