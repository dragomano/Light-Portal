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
use Bugo\LightPortal\Migrations\Columns\MediumInteger;
use Bugo\LightPortal\Migrations\Columns\SmallInteger;
use Bugo\LightPortal\Migrations\Columns\UnsignedInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Text;

class CommentsTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'comments';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$id        = new AutoIncrementInteger();
		$parentId  = new UnsignedInteger('parent_id');
		$pageId    = new SmallInteger('page_id');
		$authorId  = new MediumInteger('author_id');
		$message   = new Text('message');
		$createdAt = new UnsignedInteger('created_at');

		$createTable->addAutoIncrementColumn($id);
		$createTable->addColumn($parentId);
		$createTable->addColumn($pageId);
		$createTable->addColumn($authorId);
		$createTable->addColumn($message);
		$createTable->addColumn($createdAt);
	}
}
