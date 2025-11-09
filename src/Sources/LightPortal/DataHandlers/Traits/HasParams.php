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

namespace LightPortal\DataHandlers\Traits;

trait HasParams
{
	protected function replaceParams(array $params = [], bool $replace = true): array
	{
		if ($params === []) {
			return [];
		}

		return $this->insertData('lp_params', $params, ['item_id', 'type', 'name'], $replace);
	}
}
