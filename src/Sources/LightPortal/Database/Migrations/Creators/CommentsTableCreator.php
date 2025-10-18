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
use LightPortal\Database\Migrations\Columns\MediumInteger;
use LightPortal\Database\Migrations\Columns\SmallInteger;
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;
use Laminas\Db\Sql\Ddl\Column\Text;

if (! defined('SMF'))
	die('No direct access...');

class CommentsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_comments';

	protected function defineColumns(PortalTable $table): void
	{
		$id        = new AutoIncrementInteger();
		$parentId  = new UnsignedInteger('parent_id');
		$pageId    = new SmallInteger('page_id');
		$authorId  = new MediumInteger('author_id');
		$message   = new Text('message');
		$createdAt = new UnsignedInteger('created_at');

		$table->addAutoIncrementColumn($id);
		$table->addColumn($parentId);
		$table->addColumn($pageId);
		$table->addColumn($authorId);
		$table->addColumn($message);
		$table->addColumn($createdAt);

		$table->addIndex(['created_at'], 'idx_comments_created_at');
	}
}
