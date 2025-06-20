<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace Bugo\LightPortal\Areas\Imports;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomCategoryImport extends AbstractCustomImport
{
	protected string $type = 'category';

	protected string $entity = 'categories';

	protected function getResults(array $items): array
	{
		return $this->insertData(
			'lp_categories',
			'',
			$items,
			[
				'icon'     => 'string-60',
				'priority' => 'int',
				'status'   => 'int',
			],
			['category_id'],
		);
	}
}
