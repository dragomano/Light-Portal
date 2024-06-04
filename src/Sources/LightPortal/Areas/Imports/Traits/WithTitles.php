<?php declare(strict_types=1);

/**
 * CanReplaceTitles.php
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

trait WithTitles
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
