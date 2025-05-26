<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Imports;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomBlockImport extends AbstractCustomImport
{
	protected string $type = 'block';

	protected string $entity = 'blocks';

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
