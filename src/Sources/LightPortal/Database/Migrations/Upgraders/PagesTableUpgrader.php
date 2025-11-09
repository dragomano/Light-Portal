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

namespace LightPortal\Database\Migrations\Upgraders;

use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

class PagesTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_pages';

	public function upgrade(): void
	{
		$this->migrateData();

		$this->addIndex('idx_pages_created_at', ['created_at']);
	}

	protected function migrateData(): void
	{
		$select = $this->sql->select($this->tableName);
		$select->columns([
			'page_id',
			'content' => new Expression("COALESCE(content, '')"),
			'description' => new Expression("COALESCE(description, '')"),
		]);

		$rows = $this->sql->execute($select);

		$this->migrateRowsToTranslations('page_id', 'page', $rows);

		$this->dropColumn('content');
		$this->dropColumn('description');
	}
}
