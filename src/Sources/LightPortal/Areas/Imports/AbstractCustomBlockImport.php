<?php declare(strict_types=1);

/**
 * AbstractCustomBlockImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\{Config, Database as Db};
use Bugo\Compat\{ErrorHandler, Sapi, Utils};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomBlockImport implements ImportInterface, CustomImportInterface
{
	use Helper;

	abstract protected function getItems(array $blocks): array;

	protected function run(): void
	{
		if ($this->request()->isEmpty('blocks') && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$blocks = $this->request('blocks') && $this->request()->hasNot('import_all') ? $this->request('blocks') : [];

		$results = $titles = [];
		$items = $this->getItems($blocks);

		$this->hook('importBlocks', [&$items, &$titles]);

		if ($items) {
			foreach ($items as $block_id => $item) {
				$titles[] = [
					'type'  => 'block',
					'lang'  => Config::$language,
					'title' => $item['title']
				];

				unset($items[$block_id]['title']);
			}

			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$temp = Db::$db->insert('',
					'{db_prefix}lp_blocks',
					[
						'type'          => 'string',
						'content'       => 'string-65534',
						'placement'     => 'string-10',
						'permissions'   => 'int',
						'status'        => 'int',
						'title_class'   => 'string',
						'content_class' => 'string'
					],
					$items[$i],
					['block_id'],
					2
				);

				Utils::$context['lp_num_queries']++;

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
						'title'   => 'string',
						'item_id' => 'int'
					],
					$titles[$i],
					['item_id', 'type', 'lang'],
					2
				);

				Utils::$context['lp_num_queries']++;
			}
		}

		if (empty($results))
			ErrorHandler::fatalLang('lp_import_failed');

		$this->cache()->flush();
	}
}