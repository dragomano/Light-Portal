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

if (! defined('SMF'))
	die('No direct access...');

class CommentsTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_comments';

	public function upgrade(): void
	{
		$this->addIndex('idx_comments_created_at', ['created_at']);
	}
}
