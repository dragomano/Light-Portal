<?php declare(strict_types=1);

/**
 * AbstractExport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Compat\Sapi;
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractExport implements ExportInterface
{
	use Helper;

	abstract protected function getData();

	abstract protected function getFile();

	protected function run(): void
	{
		if (empty($file = (string) $this->getFile()))
			return;

		Sapi::setTimeLimit();

		if (file_exists($file)) {
			ob_end_clean();

			header_remove('content-encoding');
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));

			if ($fd = fopen($file, 'rb')) {
				while (! feof($fd))
					print fread($fd, 1024);

				fclose($fd);
			}

			unlink($file);
		}

		exit;
	}

	protected function getGeneratorFrom(array $items): \Closure
	{
		return static fn() => yield from $items;
	}
}