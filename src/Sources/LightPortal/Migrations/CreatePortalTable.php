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

namespace Bugo\LightPortal\Migrations;

use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Ddl\CreateTable;

if (! defined('SMF'))
	die('No direct access...');

class CreatePortalTable extends CreateTable
{
	public function addAutoIncrementColumn($column): static
	{
		$this->addColumn($column);
		$this->addPrimaryKey($column->getName());

		return $this;
	}

	public function addPrimaryKey($column): static
	{
		$pk = new PrimaryKey($column);
		$this->addConstraint($pk);

		return $this;
	}

	public function addUniqueColumn($column, $indexName = null): static
	{
		$this->addColumn($column);
		$this->addUniqueKey($column->getName(), $indexName);

		return $this;
	}

	public function addUniqueKey($column, $name = null): static
	{
		$uk = new UniqueKey($column);

		if ($name) {
			$uk->setName($name);
		}

		$this->addConstraint($uk);

		return $this;
	}
}
