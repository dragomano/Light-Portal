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

namespace LightPortal\Database\Migrations\Creators;

use LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use LightPortal\Database\Migrations\PortalTable;
use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

class PluginsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_plugins';

	protected function defineColumns(PortalTable $table): void
	{
		$id     = new AutoIncrementInteger();
		$name   = new Varchar('name', 100, false);
		$config = new Varchar('config', 100, false);
		$value  = new Text('value', nullable: true);

		$table->addAutoIncrementColumn($id);
		$table->addColumn($name);
		$table->addColumn($config);
		$table->addColumn($value);

		$compositeUniqueKey = new UniqueKey(['name', 'config']);
		$table->addConstraint($compositeUniqueKey);
	}

	public function insertDefaultData(): void
	{
		$select = $this->sql->select($this->tableName);
		$select->columns(['count' => new Expression('COUNT(*)')], false);
		$select->where(['name' => 'hello_portal', 'config' => 'keyboard_navigation']);

		$result = $this->sql->execute($select);

		$row = $result->current();

		if ($row['count'] == 0) {
			$insert = $this->sql->insert($this->tableName);

			$values = [
				['hello_portal', 'keyboard_navigation', '1'],
				['hello_portal', 'show_buttons', '1'],
				['hello_portal', 'show_progress', '1'],
				['hello_portal', 'theme', 'flattener'],
			];

			foreach ($values as $value) {
				$insert->columns(['name', 'config', 'value'])->values($value);

				$this->sql->execute($insert);
			}
		}
	}
}
