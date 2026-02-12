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
use Laminas\Db\Extra\Sql\Columns\MediumInteger;
use Laminas\Db\Extra\Sql\Columns\SmallInteger;
use Laminas\Db\Extra\Sql\Columns\UnsignedInteger;
use Laminas\Db\Extra\Sql\ExtendedTable;
use Laminas\Db\Extra\Sql\Migrations\AbstractTableCreator;

if (! defined('SMF'))
	die('No direct access...');

class CommentsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_comments';

	protected function defineColumns(ExtendedTable $table): void
	{
		$id        = new AutoIncrementInteger();
		$parentId  = new UnsignedInteger('parent_id');
		$pageId    = new SmallInteger('page_id');
		$authorId  = new MediumInteger('author_id');
		$createdAt = new UnsignedInteger('created_at');
		$updatedAt = new UnsignedInteger('updated_at');

		$table->addAutoIncrementColumn($id);
		$table->addColumn($parentId);
		$table->addColumn($pageId);
		$table->addColumn($authorId);
		$table->addColumn($createdAt);
		$table->addColumn($updatedAt);

		$table->addIndex('idx_comments_created_at', ['created_at']);
		$table->addIndex('idx_comments_updated_at', ['updated_at']);
	}

	protected function getDefaultData(): array
	{
		return [];
	}
}
