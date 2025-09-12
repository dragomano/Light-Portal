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

class AutoIncrementInteger extends UnsignedInteger
{
	public function __construct($name = 'id', $nullable = false, $default = null, array $options = [])
	{
		parent::__construct($name, $nullable, $default, $options);

		$defaultOptions = [
			'autoincrement' => true,
		];

		$this->setOptions(array_merge($defaultOptions, $options));
	}
}
