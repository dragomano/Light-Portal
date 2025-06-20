<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 3.0
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Sapi;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\Traits\CanInsertDataTrait;
use Bugo\LightPortal\Areas\Imports\Traits\HasParams;
use Bugo\LightPortal\Areas\Imports\Traits\HasTranslations;
use Bugo\LightPortal\Areas\Imports\Traits\HasTransactions;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use SimpleXMLElement;

use function ini_get;
use function simplexml_load_file;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractImport implements ImportInterface
{
	use CanInsertDataTrait;
	use HasParams;
	use HasRequest;
	use HasTransactions;
	use HasTranslations;

	public function __construct()
	{
		Utils::$context['max_file_size'] = Sapi::memoryReturnBytes(ini_get('upload_max_filesize'));
	}

	protected function getFile(string $name = 'import_file'): SimpleXMLElement|bool
	{
		if (empty($file = $this->files()->get($name)))
			return false;

		Sapi::setTimeLimit();

		if ($file['type'] !== 'text/xml')
			return false;

		return simplexml_load_file($file['tmp_name']);
	}

	abstract protected function run(): void;
}
