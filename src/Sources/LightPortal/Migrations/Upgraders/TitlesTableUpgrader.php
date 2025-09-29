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

namespace Bugo\LightPortal\Migrations\Upgraders;

use Bugo\LightPortal\Migrations\Columns\MediumText;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;

if (! defined('SMF'))
	die('No direct access...');

class TitlesTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_titles';

	public function upgrade(): void
	{
		$this->addColumn('content', ['type' => 'mediumtext', 'nullable' => true]);
		$this->addColumn('description', ['nullable' => true, 'size' => 510]);

		$this->changeColumn('value', 'title');
		$this->renameTable('lp_translations');
	}

	protected function defineColumn(string $columnName, array $params = []): ColumnInterface
	{
		$type = $params['type'] ?? 'varchar';

		if ($type === 'mediumtext') {
			return new MediumText($columnName, $params['nullable'] ?? false, $params['default'] ?? null);
		}

		return parent::defineColumn($columnName, $params);
	}
}
