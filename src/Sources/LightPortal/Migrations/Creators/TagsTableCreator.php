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
use Bugo\LightPortal\Migrations\Columns\TinyInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Varchar;

if (! defined('SMF'))
	die('No direct access...');

class TagsTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'tags';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$tagId  = new AutoIncrementInteger('tag_id');
		$slug   = new Varchar('slug', 255);
		$icon   = new Varchar('icon', 255, true);
		$status = new TinyInteger('status', default: 1);

		$createTable->addAutoIncrementColumn($tagId);
		$createTable->addUniqueColumn($slug);
		$createTable->addColumn($icon);
		$createTable->addColumn($status);
	}
}
