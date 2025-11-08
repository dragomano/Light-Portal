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

use Laminas\Db\Sql\Ddl\Column\Varchar;
use LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use LightPortal\Database\Migrations\Columns\MediumInteger;
use LightPortal\Database\Migrations\Columns\TinyInteger;
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;

if (! defined('SMF'))
	die('No direct access...');

class PagesTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_pages';

	protected function defineColumns(PortalTable $table): void
	{
		$id          = new AutoIncrementInteger('page_id');
		$categoryId  = new UnsignedInteger('category_id');
		$authorId    = new MediumInteger('author_id');
		$slug        = new Varchar('slug', 255);
		$type        = new Varchar('type', 10, default: ContentType::BBC->name());
		$entryType   = new Varchar('entry_type', 10, default: EntryType::DEFAULT->name());
		$permissions = new TinyInteger('permissions');
		$status      = new TinyInteger('status', default: 1);
		$numViews    = new UnsignedInteger('num_views');
		$numComments = new UnsignedInteger('num_comments');
		$createdAt   = new UnsignedInteger('created_at');
		$updatedAt   = new UnsignedInteger('updated_at');
		$deletedAt   = new UnsignedInteger('deleted_at');
		$lastComment = new UnsignedInteger('last_comment_id');

		$table->addAutoIncrementColumn($id);
		$table->addColumn($categoryId);
		$table->addColumn($authorId);
		$table->addUniqueColumn($slug);
		$table->addColumn($type);
		$table->addColumn($entryType);
		$table->addColumn($permissions);
		$table->addColumn($status);
		$table->addColumn($numViews);
		$table->addColumn($numComments);
		$table->addColumn($createdAt);
		$table->addColumn($updatedAt);
		$table->addColumn($deletedAt);
		$table->addColumn($lastComment);

		$table->addIndex('idx_pages_created_at', ['created_at']);
	}

	protected function getDefaultData(): array
	{
		return [
			['page_id' => 1],
			['page_id', 'author_id', 'slug', 'type', 'permissions', 'created_at'],
			[1, 1, 'home', 'html', 3, time()],
		];
	}
}
