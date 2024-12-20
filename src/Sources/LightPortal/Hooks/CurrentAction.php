<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;

use function in_array;

use const LP_ACTION;
use const LP_PAGE_PARAM;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

class CurrentAction
{
	use CommonChecks;
	use RequestTrait;

	public function __invoke(string &$action): void
	{
		$this->setCurrentAction($action);
		$this->setCurrentActionForMenuPages($action);
	}

	public function setCurrentAction(string &$action): void
	{
		if (empty(Config::$modSettings['lp_frontpage_mode']))
			return;

		if ($this->request()->isEmpty('action')) {
			$action = LP_ACTION;

			if (Setting::isStandaloneMode() && Config::$modSettings['lp_standalone_url'] !== $this->request()->url()) {
				$action = Action::FORUM->value;
			}

			if ($this->request()->isNotEmpty(LP_PAGE_PARAM)) {
				$action = LP_ACTION;
			}
		} else {
			$action = empty(Config::$modSettings['lp_standalone_mode']) && $this->request()->is(Action::FORUM->value)
				? Action::HOME->value
				: Utils::$context['current_action'];
		}

		if (isset(Utils::$context['current_board']) || $this->request()->is('keywords')) {
			$action = empty(Config::$modSettings['lp_standalone_mode'])
				? Action::HOME->value
				: (in_array(Action::FORUM->value, $this->getDisabledActions()) ? LP_ACTION : Action::FORUM->value);
		}
	}

	public function setCurrentActionForMenuPages(string &$action): void
	{
		if (empty(Utils::$context['lp_page']) || empty(Utils::$context['lp_menu_pages']))
			return;

		if (empty(Utils::$context['lp_menu_pages'][Utils::$context['lp_page']['id']]))
			return;

		if ($this->request()->url() === LP_PAGE_URL . Utils::$context['lp_page']['slug']) {
			$action = 'portal_page_' . $this->request(LP_PAGE_PARAM);
		}
	}
}
