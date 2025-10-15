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

namespace Bugo\LightPortal\Database;

use Bugo\LightPortal\Database\Operations\PortalDelete;
use Bugo\LightPortal\Database\Operations\PortalInsert;
use Bugo\LightPortal\Database\Operations\PortalReplace;
use Bugo\LightPortal\Database\Operations\PortalSelect;
use Bugo\LightPortal\Database\Operations\PortalUpdate;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\PreparableSqlInterface;

interface PortalSqlInterface
{
	public function getPrefix(): string;

	public function tableExists(string $table): bool;

	public function columnExists(string $table, string $column): bool;

	public function getAdapter(): PortalAdapterInterface;

	public function getTransaction(): PortalTransactionInterface;

	public function select($table = null): PortalSelect;

	public function insert($table = null): PortalInsert;

	public function update($table = null): PortalUpdate;

	public function delete($table = null): PortalDelete;

	public function replace($table = null): PortalReplace;

	public function execute(PreparableSqlInterface $sqlObject): ResultInterface;
}
