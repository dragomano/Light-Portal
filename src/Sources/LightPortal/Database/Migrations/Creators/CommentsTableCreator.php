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
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;

if (! defined('SMF'))
	die('No direct access...');

class CommentsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_comments';

	protected function defineColumns(PortalTable $table): void
	{
		$platform = $this->sql->getAdapter()->getPlatform();

		$id        = new AutoIncrementInteger(options: ['platform' => $platform]);
		$parentId  = new UnsignedInteger('parent_id');
		$pageId    = new UnsignedInteger('page_id');
		$authorId  = new UnsignedInteger('author_id');
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
