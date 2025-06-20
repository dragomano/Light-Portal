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

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\LightPortal\Actions\ActionInterface;
use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Traits\HasRequest;

use function call_user_func;

use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

class DefaultAction
{
	use HasRequest;

	public function __invoke(): mixed
	{
		return call_user_func([$this->determineAction(), 'show']);
	}

	private function determineAction(): ActionInterface
	{
		if ($this->request()->isNotEmpty(LP_PAGE_PARAM)) {
			return app(Page::class);
		}

		if (empty(Config::$modSettings['lp_frontpage_mode']) || Setting::isStandaloneMode()) {
			return app(BoardIndex::class);
		}

		return app(FrontPage::class);
	}
}
