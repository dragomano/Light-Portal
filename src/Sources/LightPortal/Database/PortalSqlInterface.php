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

use Laminas\Db\Sql\PreparableSqlInterface;
use LightPortal\Database\Operations\PortalDelete;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalReplace;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;

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

	public function execute(PreparableSqlInterface $sqlObject): ?PortalResultInterface;
}
