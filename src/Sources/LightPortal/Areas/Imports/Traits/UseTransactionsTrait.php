<?php declare(strict_types=1);

/**
 * UseTransactionsTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports\Traits;

use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\CacheTrait;

trait UseTransactionsTrait
{
	use CacheTrait;

	protected function startTransaction(array $items): void
	{
		Db::$db->transaction('begin');

		Utils::$context['import_successful'] = count($items);
	}

	protected function finishTransaction(array $results, string $type = 'blocks'): void
	{
		if ($results === []) {
			Db::$db->transaction('rollback');

			ErrorHandler::fatalLang('lp_import_failed');
		}

		Db::$db->transaction('commit');

		Utils::$context['import_successful'] = sprintf(
			Lang::$txt['lp_import_success'],
			Lang::getTxt('lp_' . $type . '_set', [$type => Utils::$context['import_successful']])
		);

		$this->cache()->flush();
	}
}
