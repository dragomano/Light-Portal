<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace LightPortal\DataHandlers\Imports\Database;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractDatabaseCategoryImport extends AbstractDatabaseImport
{
	protected string $type = 'category';

	protected string $entity = 'categories';

	protected function extractPermissions(array $row): int|array
	{
		return $row;
	}

	protected function getResults(array $items): array
	{
		return $this->insertData('lp_categories', $items);
	}
}
