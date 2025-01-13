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

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;

use function call_user_func;

use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

class DefaultAction
{
	use RequestTrait;

	public function __invoke(): mixed
	{
		if ($this->request()->isNotEmpty(LP_PAGE_PARAM)) {
			return call_user_func([app(Page::class), 'show']);
		}

		if (empty(Config::$modSettings['lp_frontpage_mode']) || Setting::isStandaloneMode()) {
			return call_user_func([app(BoardIndex::class), 'show']);
		}

		return call_user_func([app(FrontPage::class), 'show']);
	}
}
