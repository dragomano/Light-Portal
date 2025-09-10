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

namespace Bugo\LightPortal\Migrations\Creators;

use Bugo\LightPortal\Migrations\Columns\AutoIncrementInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;

class PluginsTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'plugins';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$id     = new AutoIncrementInteger();
		$name   = new Varchar('name', 100, false);
		$config = new Varchar('config', 100, false);
		$value  = new Text('value', nullable: true);

		$createTable->addAutoIncrementColumn($id);
		$createTable->addColumn($name);
		$createTable->addColumn($config);
		$createTable->addColumn($value);

		$compositeUniqueKey = new UniqueKey(['name', 'config']);
		$createTable->addConstraint($compositeUniqueKey);
	}

	public function insertDefaultData(): void
	{
		$tableName = $this->adapter->getPrefix() . 'lp_plugins';

		$select = new Select($tableName);
		$select->where(['name' => 'hello_portal', 'config' => 'keyboard_navigation']);
		$select->columns(['count' => new Expression('COUNT(*)')], false);

		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
		$row = $result->current();
		if ($row['count'] == 0) {
			$insert = new Insert($tableName);

			$values = [
				['hello_portal', 'keyboard_navigation', '1'],
				['hello_portal', 'show_buttons', '1'],
				['hello_portal', 'show_progress', '1'],
				['hello_portal', 'theme', 'flattener'],
			];

			foreach ($values as $value) {
				$insert->columns(['name', 'config', 'value'])->values($value);
				$statement = $this->sql->prepareStatementForSqlObject($insert);
				$statement->execute();
			}
		}
	}
}
