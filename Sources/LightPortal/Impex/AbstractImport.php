<?php declare(strict_types=1);

/**
 * AbstractImport.php
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
use SimpleXMLElement;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractImport implements ImportInterface
{
	use Helper;

	protected array $tempCache = [];

	abstract protected function run();

	/**
	 * @return false|SimpleXMLElement
	 */
	protected function getXmlFile(string $name = 'import_file'): SimpleXMLElement|bool
	{
		if (empty($file = $this->files($name)))
			return false;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$this->tempCache = $this->db_cache;
		$this->db_cache = [];

		if ($file['type'] !== 'text/xml')
			return false;

		return simplexml_load_file($file['tmp_name']);
	}

	protected function replaceTitles(array $titles, array &$results)
	{
		if (empty($titles) && empty($results))
			return;

		$titles = array_chunk($titles, 100);
		$count  = sizeof($titles);

		for ($i = 0; $i < $count; $i++) {
			$results = $this->smcFunc['db_insert']('replace',
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

	protected function replaceParams(array $params, array &$results)
	{
		if (empty($params) && empty($results))
			return;

		$params = array_chunk($params, 100);
		$count  = sizeof($params);

		for ($i = 0; $i < $count; $i++) {
			$results = $this->smcFunc['db_insert']('replace',
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

	protected function finish(array $results, string $type = 'blocks')
	{
		if (empty($results)) {
			$this->smcFunc['db_transaction']('rollback');
			$this->fatalLangError('lp_import_failed');
		}

		$this->smcFunc['db_transaction']('commit');

		$this->context['import_successful'] = sprintf($this->txt['lp_import_success'], $this->translate('lp_' . $type . '_set', [$type => $this->context['import_successful']]));

		// Restore the cache
		$this->db_cache = $this->tempCache;

		$this->cache()->flush();
	}
}