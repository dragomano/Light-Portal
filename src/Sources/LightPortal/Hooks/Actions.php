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
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Utils\Setting;

use const LP_ACTION;

if (! defined('SMF'))
	die('No direct access...');

class Actions
{
	use CommonChecks;

	public function __invoke(array &$actions): void
	{
		if (Setting::get('lp_frontpage_mode', 'string', '')) {
			$actions[LP_ACTION] = [false, [app(FrontPage::class), 'show']];
		}

		$actions[Action::FORUM->value] = [false, [app(BoardIndex::class), 'show']];

		if (empty(Config::$modSettings['lp_standalone_mode']))
			return;

		$this->unsetDisabledActions($actions);
		$this->redirectFromDisabledActions();
	}
}
