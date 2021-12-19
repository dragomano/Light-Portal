<?php

declare(strict_types = 1);

/**
 * AbstractOtherBlockImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.0
 */

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\{Addon, Helper};

abstract class AbstractOtherBlockImport implements ImportInterface, OtherImportInterface
{
	abstract protected function getItems(array $blocks): array;

	protected function run()
	{
		global $db_temp_cache, $db_cache, $language, $smcFunc;

		if (Helper::post()->isEmpty('blocks') && Helper::post()->has('import_all') === false)
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$db_temp_cache = $db_cache;
		$db_cache = [];

		$blocks = ! empty(Helper::post('blocks')) && Helper::post()->has('import_all') === false ? Helper::post('blocks') : [];

		$results = $titles = [];
		$items = $this->getItems($blocks);

		Addon::run('importBlocks', array(&$items, &$titles));

		if (! empty($items)) {
			foreach ($items as $block_id => $item) {
				$titles[] = [
					'type'  => 'block',
					'lang'  => $language,
					'title' => $item['title']
				];

				unset($items[$block_id]['title']);
			}

			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$temp = $smcFunc['db_insert']('',
					'{db_prefix}lp_blocks',
					array(
						'type'          => 'string',
						'content'       => 'string-65534',
						'placement'     => 'string-10',
						'permissions'   => 'int',
						'status'        => 'int',
						'title_class'   => 'string',
						'content_class' => 'string'
					),
					$items[$i],
					array('block_id'),
					2
				);

				$smcFunc['lp_num_queries']++;

				$results = array_merge($results, $temp);
			}
		}

		if (! empty($titles) && ! empty($results)) {
			foreach ($results as $key => $value) {
				$titles[$key]['item_id'] = $value;
			}

			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$results = $smcFunc['db_insert']('',
					'{db_prefix}lp_titles',
					array(
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string',
						'item_id' => 'int'
					),
					$titles[$i],
					array('item_id', 'type', 'lang'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (empty($results))
			fatal_lang_error('lp_import_failed', false);

		// Restore the cache
		$db_cache = $db_temp_cache;

		Helper::cache()->flush();
	}
}