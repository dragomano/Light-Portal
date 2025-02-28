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

use function explode;
use function is_array;

class PageFactory extends AbstractFactory
{
	protected string $modelClass = PageModel::class;

	protected function modifyData(array $data): array
	{
		if (! empty($data['description'])) {
			Str::cleanBbcode($data['description']);
		}

		$data['tags'] = empty($data['tags']) ? [] : $data['tags'];
		$data['tags'] = is_array($data['tags']) ? $data['tags'] : explode(',', (string) $data['tags']);

		return $data;
	}
}
