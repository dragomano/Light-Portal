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

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Utils\Str;
use stdClass;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractModel extends stdClass implements ModelInterface
{
	public function __set(string $name, mixed $value)
	{
		$camelCaseName = $this->underscoreToCamelCase($name);

		$this->$camelCaseName = $value;
	}

	public function toArray(): array
	{
		$vars = get_object_vars($this);

		$result = [];
		foreach ($vars as $key => $value) {
			$snakeName = Str::getSnakeName($key);
			$result[$snakeName] = $value;
		}

		return $result;
	}

	private function underscoreToCamelCase(string $source): string
	{
		return lcfirst(str_replace('_', '', ucwords($source, '_')));
	}
}
