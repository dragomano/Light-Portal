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

use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;

if (! defined('SMF'))
	die('No direct access...');

class ParamsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_params';

	protected function defineColumns(PortalTable $table): void
	{
		$platform = $this->sql->getAdapter()->getPlatform();

		$id     = new AutoIncrementInteger(options: ['platform' => $platform]);
		$itemId = new UnsignedInteger('item_id');
		$type   = new Varchar('type', 30, default: 'block');
		$name   = new Varchar('name', 255);
		$value  = new Text('value');

		$table->addAutoIncrementColumn($id);
		$table->addColumn($itemId);
		$table->addColumn($type);
		$table->addColumn($name);
		$table->addColumn($value);

		$compositeUniqueKey = new UniqueKey(['item_id', 'type', 'name']);
		$table->addConstraint($compositeUniqueKey);
	}

	protected function getDefaultData(): array
	{
		return [
			['id' => 1, 'item_id' => 1, 'type' => 'page', 'name' => 'show_author_and_date'],
			['id', 'item_id', 'type', 'name', 'value'],
			[1, 1, 'page', 'show_author_and_date', 0],
		];
	}
}
