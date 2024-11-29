<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas\Exports;

use Bugo\Compat\Sapi;
use Closure;

use function basename;
use function fclose;
use function feof;
use function filesize;
use function fopen;
use function fread;
use function header;
use function header_remove;
use function ob_end_clean;
use function unlink;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractExport implements ExportInterface
{
	abstract protected function getData(): array;

	abstract protected function getFile(): string;

	protected function run(): void
	{
		if (empty($file = $this->getFile()))
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

	protected function getGeneratorFrom(array $items): Closure
	{
		return static fn() => yield from $items;
	}
}
