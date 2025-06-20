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

use Bugo\Compat\Config;
use Bugo\Compat\Sapi;
use Bugo\LightPortal\Areas\Imports\Traits\CanInsertDataTrait;
use Bugo\LightPortal\Areas\Imports\Traits\HasParams;
use Bugo\LightPortal\Areas\Imports\Traits\HasTranslations;
use Bugo\LightPortal\Areas\Imports\Traits\HasTransactions;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomImport implements ImportInterface, CustomImportInterface
{
	use CanInsertDataTrait;
	use HasEvents;
	use HasParams;
	use HasTranslations;
	use HasTransactions;
	use HasRequest;

	abstract protected function getItems(array $ids): array;

	abstract protected function getResults(array $items): array;

	protected function importItems(array $items): array
	{
		$translations = [];
		foreach ($items as $id => $item) {
			$translations[] = [
				'type'        => $this->type,
				'lang'        => Config::$language,
				'title'       => $item['title'] ?? '',
				'content'     => $item['content'] ?? '',
				'description' => $item['description'] ?? '',
			];

			unset($items[$id]['title'], $items[$id]['content'], $items[$id]['description']);
		}

		$results = $this->getResults($items);

		if ($translations && $results) {
			foreach ($results as $key => $value) {
				$translations[$key]['item_id'] = $value;
			}

			$this->replaceTranslations($translations, $results, '');
		}

		return $results;
	}

	protected function run(): void
	{
		if ($this->request()->isEmpty($this->entity) && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$data = $this->request()->get($this->entity) && $this->request()->hasNot('import_all')
			? $this->request()->get($this->entity)
			: [];

		$items = $this->getItems($data);

		$this->startTransaction($items);

		$results = $this->importItems($items);

		$this->finishTransaction($results);
	}
}
