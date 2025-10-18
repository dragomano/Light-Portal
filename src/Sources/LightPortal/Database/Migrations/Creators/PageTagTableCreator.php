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

use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;

if (! defined('SMF'))
	die('No direct access...');

class PageTagTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_page_tag';

	protected function defineColumns(PortalTable $table): void
	{
		$pageId = new UnsignedInteger('page_id');
		$tagId  = new UnsignedInteger('tag_id');

		$table->addColumn($pageId);
		$table->addColumn($tagId);

		$compositePrimaryKey = new PrimaryKey(['page_id', 'tag_id']);
		$table->addConstraint($compositePrimaryKey);
	}
}
