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

use Bugo\Compat\Config;
use Bugo\LightPortal\Migrations\Columns\AutoIncrementInteger;
use Bugo\LightPortal\Migrations\Columns\UnsignedInteger;
use Bugo\LightPortal\Migrations\CreatePortalTable;
use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;

class TranslationsTableCreator extends AbstractTableCreator
{
	protected function getTableSuffix(): string
	{
		return 'translations';
	}

	protected function defineColumns(CreatePortalTable $createTable): void
	{
		$id          = new AutoIncrementInteger();
		$itemId      = new UnsignedInteger('item_id');
		$type        = new Varchar('type', 30, default: 'block');
		$lang        = new Varchar('lang', 20);
		$title       = new Varchar('title', 255, true);
		$content     = new Text('content', nullable: true);
		$description = new Varchar('description', 510, true);

		$createTable->addAutoIncrementColumn($id);
		$createTable->addColumn($itemId);
		$createTable->addColumn($type);
		$createTable->addColumn($lang);
		$createTable->addColumn($title);
		$createTable->addColumn($content);
		$createTable->addColumn($description);

		$compositeUniqueKey = new UniqueKey(['item_id', 'type', 'lang']);
		$createTable->addConstraint($compositeUniqueKey);
	}

	public function insertDefaultData(): void
	{
		$this->insertDefaultIfNotExists(
			['item_id' => 1, 'type' => 'page', 'lang' => Config::$language],
			['item_id', 'type', 'lang', 'title', 'content'],
			[1, 'page', Config::$language, Config::$mbname, '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>']
		);
	}
}
