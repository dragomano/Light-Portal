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

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\Migrations\Columns\AutoIncrementInteger;
use Bugo\LightPortal\Migrations\Columns\TinyInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Varchar;

if (! defined('SMF'))
	die('No direct access...');

class BlocksTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'blocks';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
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

		$createTable->addAutoIncrementColumn($id);
		$createTable->addColumn($icon);
		$createTable->addColumn($type);
		$createTable->addColumn($placement);
		$createTable->addColumn($priority);
		$createTable->addColumn($permissions);
		$createTable->addColumn($status);
		$createTable->addColumn($areas);
		$createTable->addColumn($titleClass);
		$createTable->addColumn($contentClass);
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
