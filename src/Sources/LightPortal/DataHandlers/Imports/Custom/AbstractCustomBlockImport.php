<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace Bugo\LightPortal\DataHandlers\Imports\Custom;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomBlockImport extends AbstractCustomImport
{
	protected string $type = 'block';

	protected string $entity = 'blocks';

	abstract protected function getType(string $type): string;

	abstract protected function getPlacement(string $col): string;

	abstract protected function extractPermissions(array $row): int|array;

	protected function getResults(array $items): array
	{
		return $this->insertData(
			'lp_blocks',
			'',
			$items,
			[
				'type'          => 'string',
				'placement'     => 'string',
				'permissions'   => 'int',
				'status'        => 'int',
				'title_class'   => 'string',
				'content_class' => 'string',
			],
			['block_id'],
		);
	}
}
