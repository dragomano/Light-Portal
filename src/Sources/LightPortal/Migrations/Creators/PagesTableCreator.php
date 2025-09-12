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
use Bugo\LightPortal\Migrations\Columns\UnsignedInteger;
use Bugo\LightPortal\Migrations\Columns\TinyInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Varchar;

if (! defined('SMF'))
	die('No direct access...');

class PagesTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'pages';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$id          = new AutoIncrementInteger('page_id');
		$categoryId  = new UnsignedInteger('category_id');
		$authorId    = new MediumInteger('author_id');
		$slug        = new Varchar('slug', 255);
		$type        = new Varchar('type', 10, default: 'bbc');
		$entryType   = new Varchar('entry_type', 10, default: 'default');
		$permissions = new TinyInteger('permissions');
		$status      = new TinyInteger('status', default: 1);
		$numViews    = new UnsignedInteger('num_views');
		$numComments = new UnsignedInteger('num_comments');
		$createdAt   = new UnsignedInteger('created_at');
		$updatedAt   = new UnsignedInteger('updated_at');
		$deletedAt   = new UnsignedInteger('deleted_at');
		$lastComment = new UnsignedInteger('last_comment_id');

		$createTable->addAutoIncrementColumn($id);
		$createTable->addColumn($categoryId);
		$createTable->addColumn($authorId);
		$createTable->addUniqueColumn($slug);
		$createTable->addColumn($type);
		$createTable->addColumn($entryType);
		$createTable->addColumn($permissions);
		$createTable->addColumn($status);
		$createTable->addColumn($numViews);
		$createTable->addColumn($numComments);
		$createTable->addColumn($createdAt);
		$createTable->addColumn($updatedAt);
		$createTable->addColumn($deletedAt);
		$createTable->addColumn($lastComment);
	}

	public function insertDefaultData(): void
	{
		$this->insertDefaultIfNotExists(
			['page_id' => 1],
			['page_id', 'author_id', 'slug', 'type', 'permissions', 'created_at'],
			[1, 1, 'home', 'html', 3, time()]
		);
	}
}
