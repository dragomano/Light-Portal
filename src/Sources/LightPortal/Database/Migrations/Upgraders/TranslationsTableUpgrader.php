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

if (! defined('SMF'))
	die('No direct access...');

class TranslationsTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_translations';

	public function upgrade(): void
	{
		$this->addIndex('idx_translations_entity', ['type', 'item_id', 'lang']);
		$this->addPrefixIndex('title_prefix', 'title', 100);
	}
}
