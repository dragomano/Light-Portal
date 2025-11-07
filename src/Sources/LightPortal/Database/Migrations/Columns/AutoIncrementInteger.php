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

namespace LightPortal\Database\Migrations\Columns;

use Laminas\Db\Adapter\Platform\Postgresql;
use ReflectionClass;
use ReflectionException;

class AutoIncrementInteger extends UnsignedInteger
{
	public function __construct($name = 'id', $nullable = false, $default = null, array $options = [])
	{
		$platform = $options['platform'] ?? null;

		if ($platform instanceof Postgresql) {
			parent::__construct($name, $nullable, $default, $options);

			try {
				$reflection = new ReflectionClass($this);
				$typeProp = $reflection->getParentClass()->getProperty('type');
				$typeProp->setValue($this, 'SERIAL');
			} catch (ReflectionException) {}
		} else {
			$options = array_merge(['autoincrement' => true], $options);

			parent::__construct($name, $nullable, $default, $options);
		}
	}
}
