<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;

use function in_array;

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
				$action = 'forum';
			}

			if ($this->request()->isNotEmpty(LP_PAGE_PARAM)) {
				$action = LP_ACTION;
			}
		} else {
			$action = empty(Config::$modSettings['lp_standalone_mode']) && $this->request()->is('forum')
				? 'home'
				: Utils::$context['current_action'];
		}

		if (isset(Utils::$context['current_board']) || $this->request()->is('keywords')) {
			$action = empty(Config::$modSettings['lp_standalone_mode'])
				? 'home'
				: (in_array('forum', $this->getDisabledActions()) ? LP_ACTION : 'forum');
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
