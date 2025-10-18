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

use Bugo\Compat\Utils;
use LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use LightPortal\Database\Migrations\Columns\TinyInteger;
use LightPortal\Database\Migrations\PortalTable;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\TitleClass;
use Laminas\Db\Sql\Ddl\Column\Varchar;

if (! defined('SMF'))
	die('No direct access...');

class BlocksTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_blocks';

	protected function defineColumns(PortalTable $table): void
	{
		$id           = new AutoIncrementInteger('block_id');
		$icon         = new Varchar('icon', 60, true);
		$type         = new Varchar('type', 30);
		$placement    = new Varchar('placement', 10);
		$priority     = new TinyInteger('priority');
		$permissions  = new TinyInteger('permissions');
		$status       = new TinyInteger('status', default: 1);
		$areas        = new Varchar('areas', 255, default: 'all');
		$titleClass   = new Varchar('title_class', 255, true);
		$contentClass = new Varchar('content_class', 255, true);

		$table->addAutoIncrementColumn($id);
		$table->addColumn($icon);
		$table->addColumn($type);
		$table->addColumn($placement);
		$table->addColumn($priority);
		$table->addColumn($permissions);
		$table->addColumn($status);
		$table->addColumn($areas);
		$table->addColumn($titleClass);
		$table->addColumn($contentClass);
	}

	public function insertDefaultData(): void
	{
		$this->insertDefaultIfNotExists(
			['block_id' => 1],
			['block_id', 'icon', 'type', 'placement', 'permissions', 'title_class', 'content_class'],
			[1, 'fas fa-user', 'user_info', Utils::$context['right_to_left'] ? 'left' : 'right', 3, TitleClass::first(), ContentClass::first()]
		);
	}
}
