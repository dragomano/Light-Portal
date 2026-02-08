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

namespace LightPortal\Database\Migrations\Upgraders;

use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

class CommentsTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_comments';

	public function upgrade(): void
	{
		$this->addColumn('updated_at', ['unsigned' => true, 'type' => 'int', 'default' => 0]);

		$this->migrateData();

		$this->addIndex('idx_comments_created_at', ['created_at']);
		$this->addIndex('idx_comments_updated_at', ['updated_at']);
	}

	protected function migrateData(): void
	{
		$select = $this->sql->select('lp_comments');
		$select->columns([
			'id',
			'content' => new Expression("COALESCE(message, '')"),
		]);

		$rows = $this->sql->execute($select);

		$this->migrateRowsToTranslations('id', 'comment', $rows);

		$this->dropColumn('message');
	}
}
