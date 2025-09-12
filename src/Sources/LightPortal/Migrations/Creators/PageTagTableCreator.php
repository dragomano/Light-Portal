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

use Bugo\LightPortal\Migrations\Columns\UnsignedInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;

class PageTagTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'page_tag';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$pageId = new UnsignedInteger('page_id');
		$tagId  = new UnsignedInteger('tag_id');

		$createTable->addColumn($pageId);
		$createTable->addColumn($tagId);

		$compositePrimaryKey = new PrimaryKey(['page_id', 'tag_id']);
		$createTable->addConstraint($compositePrimaryKey);
	}
}
