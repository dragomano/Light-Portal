<?php

namespace Bugo\LightPortal\Impex;

/**
 * AbstractExport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.10
 */

abstract class AbstractExport implements ExportInterface
{
	abstract protected function getData();
	abstract protected function getXmlFile();

	/**
	 * Get an export file via the user browser
	 *
	 * Получаем экспортируемый файл через браузер
	 *
	 * @return void
	 */
	protected function run()
	{
		if (empty($file = $this->getXmlFile()))
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