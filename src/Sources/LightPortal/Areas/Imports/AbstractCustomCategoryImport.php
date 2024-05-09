<?php declare(strict_types=1);

/**
 * AbstractCustomCategoryImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\{Config, Db};
use Bugo\Compat\{ErrorHandler, Sapi};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomCategoryImport implements ImportInterface, CustomImportInterface
{
	use Helper;

	abstract protected function getItems(array $categories): array;

	protected function run(): void
	{
		if ($this->request()->isEmpty('categories') && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$categories = $this->request('categories') && $this->request()->hasNot('import_all')
			? $this->request('categories')
			: [];

		$results = $titles = [];
		$items = $this->getItems($categories);

		$this->hook('importCategories', [&$items, &$titles]);

		if ($items) {
			foreach ($items as $category_id => $item) {
				$titles[] = [
					'type'  => 'category',
					'lang'  => Config::$language,
					'value' => $item['title']
				];

				unset($items[$category_id]['title']);
			}

			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$temp = Db::$db->insert('',
					'{db_prefix}lp_categories',
					[
						'icon'        => 'string-60',
						'description' => 'string-255',
						'priority'    => 'int',
						'status'      => 'int',
					],
					$items[$i],
					['category_id'],
					2
				);

				$results = array_merge($results, $temp);
			}
		}

		if ($titles && $results) {
			foreach ($results as $key => $value) {
				$titles[$key]['item_id'] = $value;
			}

			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$results = Db::$db->insert('',
					'{db_prefix}lp_titles',
					[
						'type'    => 'string',
						'lang'    => 'string',
						'value'   => 'string',
						'item_id' => 'int',
					],
					$titles[$i],
					['id'],
					2
				);
			}
		}

		if (empty($results))
			ErrorHandler::fatalLang('lp_import_failed');

		$this->cache()->flush();
	}
}