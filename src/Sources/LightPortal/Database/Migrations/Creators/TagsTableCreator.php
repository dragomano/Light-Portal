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

namespace LightPortal\Database\Migrations\Creators;

use Laminas\Db\Extra\Sql\Columns\AutoIncrementInteger;
use Laminas\Db\Extra\Sql\Columns\TinyInteger;
use Laminas\Db\Extra\Sql\ExtendedTable;
use Laminas\Db\Extra\Sql\Migrations\AbstractTableCreator;
use Laminas\Db\Sql\Ddl\Column\Varchar;

if (! defined('SMF'))
	die('No direct access...');

class TagsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_tags';

	protected function defineColumns(ExtendedTable $table): void
	{
		$tagId  = new AutoIncrementInteger('tag_id');
		$slug   = new Varchar('slug', 255);
		$icon   = new Varchar('icon', 60, true);
		$status = new TinyInteger('status', default: 1);

		$table->addAutoIncrementColumn($tagId);
		$table->addUniqueColumn($slug);
		$table->addColumn($icon);
		$table->addColumn($status);
	}

	protected function getDefaultData(): array
	{
		return [];
	}
}
