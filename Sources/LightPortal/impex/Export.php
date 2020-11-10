<?php

namespace Bugo\LightPortal\Impex;

/**
 * Export.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

abstract class Export implements IExport
{
	abstract protected static function getData();
	abstract protected static function getXmlFile();

	/**
	 * Get an export file via the user browser
	 *
	 * Получаем экспортируемый файл через браузер
	 *
	 * @return void
	 */
	protected static function run()
	{
		if (empty($file = static::getXmlFile()))
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
}