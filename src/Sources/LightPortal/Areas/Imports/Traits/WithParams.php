<?php declare(strict_types=1);

/**
 * CanReplaceParams.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports\Traits;

trait WithParams
{
	protected function replaceParams(array $params, array &$results): void
	{
		if ($params === [] || $results === [])
			return;

		$results = $this->insertData(
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
