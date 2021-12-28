<?php

declare(strict_types = 1);

/**
 * AbstractOtherPageImport.php
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

abstract class AbstractOtherPageImport implements ImportInterface, OtherImportInterface
{
	abstract protected function getItems(array $pages): array;

	protected function run()
	{
		global $db_temp_cache, $db_cache, $smcFunc;

		if (Helper::post()->isEmpty('pages') && Helper::post()->has('import_all') === false)
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$db_temp_cache = $db_cache;
		$db_cache = [];

		$pages = ! empty(Helper::post('pages')) && Helper::post()->has('import_all') === false ? Helper::post('pages') : [];

		$results = $titles = $params = $comments = [];
		$items = $this->getItems($pages);

		Addon::run('importPages', array(&$items, &$titles, &$params, &$comments));

		if (! empty($items)) {
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_pages',
					array(
						'page_id'      => 'int',
						'author_id'    => 'int',
						'alias'        => 'string-255',
						'description'  => 'string-255',
						'content'      => 'string',
						'type'         => 'string',
						'permissions'  => 'int',
						'status'       => 'int',
						'num_views'    => 'int',
						'num_comments' => 'int',
						'created_at'   => 'int',
						'updated_at'   => 'int'
					),
					$items[$i],
					array('page_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (empty($results))
			fatal_lang_error('lp_import_failed', false);

		if (! empty($titles)) {
			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$smcFunc['db_insert']('replace',
					'{db_prefix}lp_titles',
					array(
						'item_id' => 'int',
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string'
					),
					$titles[$i],
					array('item_id', 'type', 'lang'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (! empty($params)) {
			$params = array_chunk($params, 100);
			$count  = sizeof($params);

			for ($i = 0; $i < $count; $i++) {
				$smcFunc['db_insert']('replace',
					'{db_prefix}lp_params',
					array(
						'item_id' => 'int',
						'type'    => 'string',
						'name'    => 'string',
						'value'   => 'string'
					),
					$params[$i],
					array('item_id', 'type', 'name'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (! empty($comments)) {
			$tempCommentArray = [];

			foreach ($comments as $comment) {
				foreach ($comment as $com) {
					$tempCommentArray[] = $com;
				}
			}

			$comments = array_chunk($tempCommentArray, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				$smcFunc['db_insert']('replace',
					'{db_prefix}lp_comments',
					array(
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string',
						'created_at' => 'int'
					),
					$comments[$i],
					array('id', 'page_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		// Restore the cache
		$db_cache = $db_temp_cache;

		Helper::cache()->flush();
	}
}