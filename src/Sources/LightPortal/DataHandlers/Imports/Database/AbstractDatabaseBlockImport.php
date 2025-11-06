<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace LightPortal\DataHandlers\Imports\Database;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractDatabaseBlockImport extends AbstractDatabaseImport
{
	protected string $type = 'block';

	protected string $entity = 'blocks';

	abstract protected function getType(mixed $type): string;

	abstract protected function getPlacement(int $col): string;

	abstract protected function extractPermissions(array $row): int|array;

	protected function getResults(array $items): array
	{
		return $this->insertData('lp_blocks', $items);
	}
}
