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

if (! defined('SMF'))
	die('No direct access...');

class TagsTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_tags';

	public function upgrade(): void
	{
		$this->addColumn('slug', ['nullable' => true]);
	}
}
