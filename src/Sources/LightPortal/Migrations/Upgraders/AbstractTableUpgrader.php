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

namespace Bugo\LightPortal\Migrations\Upgraders;

use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalAdapterFactory;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

abstract class AbstractTableUpgrader implements TableUpgraderInterface
{
	protected ?PortalAdapter $adapter = null;

	protected ?Sql $sql = null;

	public function __construct(?PortalAdapter $adapter = null, ?Sql $sql = null)
	{
		$this->adapter = $this->adapter ?? PortalAdapterFactory::create();
		$this->sql = $sql ?? new Sql($this->adapter);
	}

	protected function getTableName(string $tableName): string
	{
		return $this->adapter->getPrefix() . 'lp_' . $tableName;
	}

	protected function executeSql($builder): void
	{
		$sqlString = $this->sql->buildSqlString($builder);
		$this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
	}
}
