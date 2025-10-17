<?php declare(strict_types=1);

/**
 * @package SimpleChat (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\SimpleChat;

use Bugo\LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use Bugo\LightPortal\Database\Migrations\Columns\UnsignedInteger;
use Bugo\LightPortal\Database\Migrations\Creators\AbstractTableCreator;
use Bugo\LightPortal\Database\Migrations\PortalTable;
use Laminas\Db\Sql\Ddl\Column\Varchar;

class Table extends AbstractTableCreator
{
	protected string $tableName = 'lp_simple_chat_messages';

	protected function defineColumns(PortalTable $table): void
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
}
