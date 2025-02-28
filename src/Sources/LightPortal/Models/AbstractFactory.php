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

abstract class AbstractFactory implements FactoryInterface
{
	protected string $modelClass;

	public function create(array $data): ModelInterface
	{
		if (! empty($data['titles'])) {
			Str::cleanBbcode($data['titles']);
		}

		$data = $this->modifyData($data);

		return new $this->modelClass($data);
	}

	protected function modifyData(array $data): array
	{
		return $data;
	}
}
