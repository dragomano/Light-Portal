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

namespace LightPortal\Database;

use Laminas\Db\Adapter\Driver\ConnectionInterface;

if (! defined('SMF'))
	die('No direct access...');

readonly class PortalTransaction implements PortalTransactionInterface
{
	private ConnectionInterface $connection;

	public function __construct(PortalAdapterInterface $adapter)
	{
		$this->connection = $adapter->getDriver()->getConnection();
	}

	public function begin(): ConnectionInterface
	{
		return $this->connection->beginTransaction();
	}

	public function rollback(): ConnectionInterface
	{
		return $this->connection->rollback();
	}

	public function commit(): ConnectionInterface
	{
		return $this->connection->commit();
	}
}
