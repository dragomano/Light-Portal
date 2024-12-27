<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;

use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

class DefaultAction
{
	use RequestTrait;

	public function __invoke(): mixed
	{
		if ($this->request()->isNotEmpty(LP_PAGE_PARAM)) {
			return app('page')->show();
		}

		if (empty(Config::$modSettings['lp_frontpage_mode']) || Setting::isStandaloneMode()) {
			return app('board_index')->show();
		}

		return app('front_page')->show();
	}
}
