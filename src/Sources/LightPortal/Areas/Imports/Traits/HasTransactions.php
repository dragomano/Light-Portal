<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Imports\Traits;

use Bugo\Compat\Db;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Traits\HasCache;

use function count;
use function sprintf;

trait HasTransactions
{
	use HasCache;

	protected function startTransaction(array $items): void
	{
		Db::$db->transaction('begin');

		Utils::$context['import_successful'] = count($items);
	}

	protected function finishTransaction(array $results): void
	{
		if ($results === []) {
			Db::$db->transaction('rollback');

			ErrorHandler::fatalLang('lp_import_failed', false);
		}

		Db::$db->transaction();

		Utils::$context['import_successful'] = sprintf(
			Lang::$txt['lp_import_success'],
			Lang::getTxt('lp_' . $this->entity . '_set', [$this->entity => Utils::$context['import_successful']])
		);

		$this->cache()->flush();
	}
}
