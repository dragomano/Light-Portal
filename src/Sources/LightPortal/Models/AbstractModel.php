<?php declare(strict_types=1);

/**
 * AbstractModel.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\Str;
use stdClass;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractModel extends stdClass
{
	use Helper;

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
