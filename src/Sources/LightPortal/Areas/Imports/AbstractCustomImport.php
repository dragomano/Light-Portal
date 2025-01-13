<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\Sapi;
use Bugo\LightPortal\Areas\Imports\Traits\CanInsertDataTrait;
use Bugo\LightPortal\Areas\Imports\Traits\WithParamsTrait;
use Bugo\LightPortal\Areas\Imports\Traits\WithTitlesTrait;
use Bugo\LightPortal\Areas\Imports\Traits\UseTransactionsTrait;
use Bugo\LightPortal\Utils\RequestTrait;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomImport implements ImportInterface, CustomImportInterface
{
	use CanInsertDataTrait;
	use RequestTrait;
	use UseTransactionsTrait;
	use WithParamsTrait;
	use WithTitlesTrait;

	abstract protected function getItems(array $ids): array;

	abstract protected function importItems(array &$items, array &$titles): array;

	protected function run(): void
	{
		if ($this->request()->isEmpty($this->entity) && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$data = $this->request()->get($this->entity) && $this->request()->hasNot('import_all')
			? $this->request()->get($this->entity)
			: [];

		$items = $this->getItems($data);

		$titles = [];

		$this->startTransaction($items);

		$results = $this->importItems($items, $titles);

		$this->finishTransaction($results);
	}
}
