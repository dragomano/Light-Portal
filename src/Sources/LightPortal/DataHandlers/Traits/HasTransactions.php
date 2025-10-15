<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\DataHandlers\Traits;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Traits\HasCache;

trait HasTransactions
{
	use HasCache;

	protected function startTransaction(array $items): void
	{
		$this->sql->getTransaction()->begin();

		Utils::$context['import_successful'] = count($items);
	}

	protected function finishTransaction(array $results): void
	{
		if ($results === [] && Utils::$context['import_successful'] === 0) {
			$this->sql->getTransaction()->rollback();

			$this->errorHandler->fatal('lp_import_failed', false);
		} else {
			$this->sql->getTransaction()->commit();
		}

		$entityText = Lang::getTxt('lp_' . $this->entity . '_set', [
			$this->entity => Utils::$context['import_successful']
		]);

		Utils::$context['import_successful'] = sprintf(Lang::$txt['lp_import_success'], $entityText);

		$this->cache()->flush();
	}
}
