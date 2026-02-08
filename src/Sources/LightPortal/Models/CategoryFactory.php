<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Models;

use LightPortal\Enums\Status;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class CategoryFactory extends AbstractFactory
{
	protected string $modelClass = CategoryModel::class;

	protected function populate(array $data): array
	{
		$data['status'] ??= Status::ACTIVE->value;

		if (! empty($data['description'])) {
			Str::cleanBbcode($data['description']);
		}

		return $data;
	}
}
