<?php declare(strict_types=1);

/**
 * AbstractCustomImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Sapi;
use Bugo\LightPortal\Areas\Imports\Traits\CanInsertData;
use Bugo\LightPortal\Areas\Imports\Traits\WithParams;
use Bugo\LightPortal\Areas\Imports\Traits\WithTitles;
use Bugo\LightPortal\Areas\Imports\Traits\UseTransactions;
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomImport implements ImportInterface, CustomImportInterface
{
	use Helper;
	use CanInsertData;
	use WithParams;
	use WithTitles;
	use UseTransactions;

	abstract protected function getItems(array $ids): array;

	abstract protected function importItems(array &$items, array &$titles): array;

	protected function run(): void
	{
		if ($this->request()->isEmpty($this->entity) && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$data = $this->request($this->entity) && $this->request()->hasNot('import_all')
			? $this->request($this->entity)
			: [];

		$items = $this->getItems($data);

		$titles = [];

		$this->startTransaction($items);

		$results = $this->importItems($items, $titles);

		$this->finishTransaction($results, $this->entity);
	}
}
