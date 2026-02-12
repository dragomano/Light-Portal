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

namespace LightPortal\Database;

use Bugo\Compat\ErrorHandler;
use Laminas\Db\Extra\Sql\ExtendedSql;
use Throwable;

if (! defined('SMF'))
	die('No direct access...');

class PortalSql extends ExtendedSql implements PortalSqlInterface
{
	public function __construct(PortalAdapterInterface $adapter)
	{
		parent::__construct($adapter);
	}

	public function getAdapter(): PortalAdapterInterface
	{
		return $this->adapter;
	}

	protected function handleExecutionError(Throwable $e): void
	{
		$profiler = $this->getAdapter()->getProfiler();
		$profiles = $profiler->getProfiles();

		$sql  = $profiles[count($profiles) - 1]['sql'] ?? '';
		$file = $e->getTrace()[1]['file'] ?? '';
		$line = $e->getTrace()[1]['line'] ?? '';

		ErrorHandler::log(
			'[LP] queries: ' . $e->getMessage() . PHP_EOL . PHP_EOL . $sql,
			file: $file,
			line: $line
		);

		ErrorHandler::fatal($e->getMessage(), false);
	}
}
