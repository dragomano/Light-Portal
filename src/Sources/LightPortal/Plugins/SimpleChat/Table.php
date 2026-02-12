<?php declare(strict_types=1);

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2026 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 12.02.26
 */

namespace LightPortal\Plugins\SimpleChat;

use Laminas\Db\Extra\Sql\Columns\AutoIncrementInteger;
use Laminas\Db\Extra\Sql\Columns\UnsignedInteger;
use Laminas\Db\Extra\Sql\ExtendedTable;
use Laminas\Db\Extra\Sql\Migrations\AbstractTableCreator;
use Laminas\Db\Sql\Ddl\Column\Varchar;

class Table extends AbstractTableCreator
{
	protected string $tableName = 'lp_simple_chat_messages';

	protected function defineColumns(ExtendedTable $table): void
	{
		$id        = new AutoIncrementInteger('id');
		$blockId   = new UnsignedInteger('block_id');
		$userId    = new UnsignedInteger('user_id');
		$message   = new Varchar('message', 255);
		$createdAt = new UnsignedInteger('created_at');

		$table->addAutoIncrementColumn($id);
		$table->addColumn($blockId);
		$table->addColumn($userId);
		$table->addColumn($message);
		$table->addColumn($createdAt);
	}

	protected function getDefaultData(): array
	{
		return [];
	}
}
