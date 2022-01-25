<?php declare(strict_types=1);

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

use Bugo\LightPortal\Helper;

use function fatal_lang_error;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractOtherBlockImport implements ImportInterface, OtherImportInterface
{
	use Helper;

	abstract protected function getItems(array $blocks): array;

	protected function run()
	{
		if ($this->post()->isEmpty('blocks') && $this->post()->has('import_all') === false)
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$this->db_temp_cache = $this->db_cache;
		$this->db_cache = [];

		$blocks = $this->post('blocks') && $this->post()->has('import_all') === false ? $this->post('blocks') : [];

		$results = $titles = [];
		$items = $this->getItems($blocks);

		$this->hook('importBlocks', [&$items, &$titles]);

		if ($items) {
			foreach ($items as $block_id => $item) {
				$titles[] = [
					'type'  => 'block',
					'lang'  => $this->language,
					'title' => $item['title']
				];

				unset($items[$block_id]['title']);
			}

			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$temp = $this->smcFunc['db_insert']('',
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

				$this->context['lp_num_queries']++;

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
				$results = $this->smcFunc['db_insert']('',
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

				$this->context['lp_num_queries']++;
			}
		}

		if (empty($results))
			fatal_lang_error('lp_import_failed', false);

		// Restore the cache
		$this->db_cache = $this->db_temp_cache;

		$this->cache()->flush();
	}
}