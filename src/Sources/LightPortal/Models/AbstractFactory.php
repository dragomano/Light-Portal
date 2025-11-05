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

namespace LightPortal\Models;

use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractFactory implements FactoryInterface
{
	protected string $modelClass;

	public function create(array $data): ModelInterface
	{
		if (! empty($data['title'])) {
			Str::cleanBbcode($data['title']);
		}

		$data = $this->populate($data);

		return new $this->modelClass($data);
	}

	protected function populate(array $data): array
	{
		return $data;
	}
}
