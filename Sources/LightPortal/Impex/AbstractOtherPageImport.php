<?php declare(strict_types=1);

/**
 * AbstractOtherPageImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.1
 */

namespace Bugo\LightPortal\Impex;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractOtherPageImport implements ImportInterface, OtherImportInterface
{
	use Helper;

	protected array $tempCache = [];

	abstract protected function getItems(array $pages): array;

	protected function run()
	{
		if ($this->request()->isEmpty('pages') && $this->request()->hasNot('import_all'))
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$this->tempCache = $this->db_cache;
		$this->db_cache = [];

		$pages = $this->request('pages') && $this->request()->hasNot('import_all') ? $this->request('pages') : [];

		$results = $titles = $params = $comments = [];
		$items = $this->getItems($pages);

		$this->hook('importPages', [&$items, &$titles, &$params, &$comments]);

		if ($items) {
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_pages',
					[
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
					],
					$items[$i],
					['page_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if (empty($results))
			$this->fatalLangError('lp_import_failed');

		if ($titles) {
			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_titles',
					[
						'item_id' => 'int',
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string'
					],
					$titles[$i],
					['item_id', 'type', 'lang'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if ($params) {
			$params = array_chunk($params, 100);
			$count  = sizeof($params);

			for ($i = 0; $i < $count; $i++) {
				$this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_params',
					[
						'item_id' => 'int',
						'type'    => 'string',
						'name'    => 'string',
						'value'   => 'string'
					],
					$params[$i],
					['item_id', 'type', 'name'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if ($comments) {
			$tempCommentArray = [];

			foreach ($comments as $comment) {
				foreach ($comment as $com) {
					$tempCommentArray[] = $com;
				}
			}

			$comments = array_chunk($tempCommentArray, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				$this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_comments',
					[
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string',
						'created_at' => 'int'
					],
					$comments[$i],
					['id', 'page_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		// Restore the cache
		$this->db_cache = $this->tempCache;

		$this->cache()->flush();
	}
}