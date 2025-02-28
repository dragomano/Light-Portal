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

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Utils\Str;

class BlockFactory extends AbstractFactory
{
	protected string $modelClass = BlockModel::class;

	protected function modifyData(array $data): array
	{
		if (! empty($data['note'])) {
			Str::cleanBbcode($data['note']);
		}

		return $data;
	}
}
