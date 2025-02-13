<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Imports\Traits;

trait HasTitles
{
	protected function replaceTitles(array $titles, array &$results, string $method = 'replace'): void
	{
		if ($titles === [] || $results === [])
			return;

		$results = $this->insertData(
			'lp_titles',
			$method,
			$titles,
			$method === 'replace' ? [
				'item_id' => 'int',
				'type'    => 'string',
				'lang'    => 'string',
				'value'   => 'string',
			] : [
				'type'    => 'string',
				'lang'    => 'string',
				'value'   => 'string',
				'item_id' => 'int',
			],
			$method === 'replace' ? ['item_id', 'type', 'lang'] : ['id'],
		);
	}
}
