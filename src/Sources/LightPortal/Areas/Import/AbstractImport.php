<?php declare(strict_types=1);

/**
 * AbstractImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Import;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Config, ErrorHandler, Lang, Utils};
use SimpleXMLElement;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractImport implements ImportInterface
{
	use Helper;

	public function __construct()
	{
		Utils::$context['max_file_size'] = Config::memoryReturnBytes(ini_get('upload_max_filesize'));
	}

	protected function getFile(string $name = 'import_file'): SimpleXMLElement|bool
	{
		if (empty($file = $this->files($name)))
			return false;

		// Might take some time.
		@set_time_limit(600);

		if ($file['type'] !== 'text/xml')
			return false;

		return simplexml_load_file($file['tmp_name']);
	}

	protected function replaceTitles(array $titles, array &$results): void
	{
		if (empty($titles) && empty($results))
			return;

		$titles = array_chunk($titles, 100);
		$count  = sizeof($titles);

		for ($i = 0; $i < $count; $i++) {
			$results = Utils::$smcFunc['db_insert']('replace',
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

			Utils::$context['lp_num_queries']++;
		}
	}

	protected function replaceParams(array $params, array &$results): void
	{
		if (empty($params) && empty($results))
			return;

		$params = array_chunk($params, 100);
		$count  = sizeof($params);

		for ($i = 0; $i < $count; $i++) {
			$results = Utils::$smcFunc['db_insert']('replace',
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

			Utils::$context['lp_num_queries']++;
		}
	}

	protected function finish(array $results, string $type = 'blocks'): void
	{
		if (empty($results)) {
			Utils::$smcFunc['db_transaction']('rollback');
			ErrorHandler::fatalLang('lp_import_failed');
		}

		Utils::$smcFunc['db_transaction']('commit');

		Utils::$context['import_successful'] = sprintf(Lang::$txt['lp_import_success'], $this->translate('lp_' . $type . '_set', [$type => Utils::$context['import_successful']]));

		$this->cache()->flush();
	}

	abstract protected function run();
}