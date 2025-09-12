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
use Bugo\LightPortal\Migrations\Columns\UnsignedInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;

if (! defined('SMF'))
	die('No direct access...');

class ParamsTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'params';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$id     = new AutoIncrementInteger();
		$itemId = new UnsignedInteger('item_id');
		$type   = new Varchar('type', 30, default: 'block');
		$name   = new Varchar('name', 255);
		$value  = new Text('value');

		$createTable->addAutoIncrementColumn($id);
		$createTable->addColumn($itemId);
		$createTable->addColumn($type);
		$createTable->addColumn($name);
		$createTable->addColumn($value);

		$compositeUniqueKey = new UniqueKey(['item_id', 'type', 'name']);
		$createTable->addConstraint($compositeUniqueKey);
	}

	public function insertDefaultData(): void
	{
		$this->insertDefaultIfNotExists(
			['item_id' => 1, 'type' => 'page', 'name' => 'show_author_and_date'],
			['item_id', 'type', 'name', 'value'],
			[1, 'page', 'show_author_and_date', 0]
		);
	}
}
