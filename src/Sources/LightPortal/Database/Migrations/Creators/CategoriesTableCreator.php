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

use Laminas\Db\Sql\Ddl\Column\Varchar;
use LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use LightPortal\Database\Migrations\Columns\TinyInteger;
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;

if (! defined('SMF'))
	die('No direct access...');

class CategoriesTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_categories';

	protected function defineColumns(PortalTable $table): void
	{
		$id       = new AutoIncrementInteger('category_id');
		$parentId = new UnsignedInteger('parent_id');
		$slug     = new Varchar('slug', 255);
		$icon     = new Varchar('icon', 60, true);
		$priority = new TinyInteger('priority');
		$status   = new TinyInteger('status', default: 1);

		$table->addAutoIncrementColumn($id);
		$table->addColumn($parentId);
		$table->addUniqueColumn($slug);
		$table->addColumn($icon);
		$table->addColumn($priority);
		$table->addColumn($status);
	}

	protected function getDefaultData() : array
	{
		return [];
	}
}
