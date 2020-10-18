<?php

namespace Bugo\LightPortal\Impex;

/**
 * Import.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

abstract class Import
{
	abstract protected static function run();

	/**
	 * Getting a part of an SQL expression like "(value1, value2, value3)"
	 *
	 * Получаем часть SQL-выражения вида "(value1, value2, value3)"
	 *
	 * @param array $items
	 * @return string
	 */
	protected static function getValues(array $items)
	{
		if (empty($items))
			return '';

		$result = '';
		$cnt = count($items);
		for ($i = 0; $i < $cnt; $i++) {
			if ($i > 0)
				$result .= ', ';

			$result .= "('" . implode("', '", $items[$i]) . "')";
		}

		return $result;
	}
}