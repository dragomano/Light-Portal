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

use Bugo\Compat\Config;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use LightPortal\Database\Migrations\Columns\AutoIncrementInteger;
use LightPortal\Database\Migrations\Columns\MediumText;
use LightPortal\Database\Migrations\Columns\UnsignedInteger;
use LightPortal\Database\Migrations\PortalTable;

if (! defined('SMF'))
	die('No direct access...');

class TranslationsTableCreator extends AbstractTableCreator
{
	protected string $tableName = 'lp_translations';

	protected function defineColumns(PortalTable $table): void
	{
		$id          = new AutoIncrementInteger();
		$itemId      = new UnsignedInteger('item_id');
		$type        = new Varchar('type', 30, default: 'block');
		$lang        = new Varchar('lang', 20);
		$title       = new Varchar('title', 255, true);
		$content     = new MediumText('content', nullable: true);
		$description = new Varchar('description', 510, true);

		$table->addAutoIncrementColumn($id);
		$table->addColumn($itemId);
		$table->addColumn($type);
		$table->addColumn($lang);
		$table->addColumn($title);
		$table->addColumn($content);
		$table->addColumn($description);

		$compositeUniqueKey = new UniqueKey(['item_id', 'type', 'lang']);
		$table->addConstraint($compositeUniqueKey);
	}

	protected function getDefaultData(): array
	{
		return [
			['id' => 1, 'item_id' => 1, 'type' => 'page', 'lang' => Config::$language],
			['id', 'item_id', 'type', 'lang', 'title', 'content'],
			[
				1,
				1,
				'page',
				Config::$language,
				Config::$mbname,
				'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
			],
		];
	}
}
