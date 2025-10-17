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

namespace Bugo\LightPortal\Database\Migrations\Upgraders;

use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

class BlocksTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_blocks';

	public function upgrade(): void
	{
		$this->migrateData();
	}

	protected function migrateData(): void
	{
		$select = $this->sql->select($this->tableName);
		$select->columns([
			'block_id',
			'content' => new Expression("COALESCE(content, '')"),
			'description' => new Expression("COALESCE(note, '')"),
		]);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			$this->migrateRowToTranslations($row['block_id'], 'block', $row['content'], $row['description']);
		}

		$this->dropColumn('content');
		$this->dropColumn('note');
	}
}
