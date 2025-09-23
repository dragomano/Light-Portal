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

namespace Bugo\LightPortal\DataHandlers\Traits;

trait HasParams
{
	protected function replaceParams(array $params, array $results): array
	{
		if ($params === [] || $results === [])
			return [];

		return $this->insertData(
			'lp_params',
			'replace',
			$params,
			[
				'item_id' => 'int',
				'type'    => 'string',
				'name'    => 'string',
				'value'   => 'string',
			],
			['item_id', 'type', 'name'],
		);
	}
}
