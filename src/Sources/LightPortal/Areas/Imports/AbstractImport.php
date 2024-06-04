<?php declare(strict_types=1);

/**
 * AbstractImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\{Sapi, Utils};
use Bugo\LightPortal\Areas\Imports\Traits\CanInsertData;
use Bugo\LightPortal\Areas\Imports\Traits\WithParams;
use Bugo\LightPortal\Areas\Imports\Traits\WithTitles;
use Bugo\LightPortal\Areas\Imports\Traits\UseTransactions;
use Bugo\LightPortal\Helper;
use SimpleXMLElement;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractImport implements ImportInterface
{
	use Helper;
	use CanInsertData;
	use WithParams;
	use WithTitles;
	use UseTransactions;

	public function __construct()
	{
		Utils::$context['max_file_size'] = Sapi::memoryReturnBytes(ini_get('upload_max_filesize'));
	}

	protected function getFile(string $name = 'import_file'): SimpleXMLElement|bool
	{
		if (empty($file = $this->files($name)))
			return false;

		Sapi::setTimeLimit();

		if ($file['type'] !== 'text/xml')
			return false;

		return simplexml_load_file($file['tmp_name']);
	}

	abstract protected function run();
}
