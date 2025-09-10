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

namespace Bugo\LightPortal\Migrations\Columns;

use Laminas\Db\Sql\Ddl\Column\Integer;

class UnsignedInteger extends Integer
{
	public function __construct($name = null, $nullable = false, $default = 0, array $options = [])
	{
		parent::__construct($name, $nullable, $default, $options);

		$defaultOptions = [
			'unsigned' => true,
		];

		$this->setOptions(array_merge($defaultOptions, $options));
	}
}
