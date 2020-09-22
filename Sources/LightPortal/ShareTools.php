<?php

namespace Bugo\LightPortal;

/**
 * ShareTools.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
 */

trait ShareTools
{
	/**
	 * Get an export file via the user browser
	 *
	 * Получаем экспортируемый файл через браузер
	 *
	 * @param string $file
	 * @return void
	 */
	public static function runExport(string $file)
	{
		if (empty($file))
			return;

		// Might take some time.
		@set_time_limit(600);

		if (file_exists($file)) {
			if (ob_get_level())
				ob_end_clean();

			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));

			if ($fd = fopen($file, 'rb')) {
				while (!feof($fd))
					print fread($fd, 1024);

				fclose($fd);
			}

			unlink($file);
		}

		exit;
	}

	/**
	 * Getting a part of an SQL expression like "(value1, value2, value3)"
	 *
	 * Получаем часть SQL-выражения вида "(value1, value2, value3)"
	 *
	 * @param array $items
	 * @return string
	 */
	public static function getValues(array $items)
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