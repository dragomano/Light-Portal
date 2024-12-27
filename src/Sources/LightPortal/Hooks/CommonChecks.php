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
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;

use function array_flip;
use function array_key_exists;
use function array_keys;
use function defined;

if (! defined('SMF'))
	die('No direct access...');

trait CommonChecks
{
	use RequestTrait;

	protected function isPortalCanBeLoaded(): bool
	{
		if (! defined('LP_NAME') || isset(Utils::$context['uninstalling']) || $this->request()->is('printpage')) {
			Config::$modSettings['minimize_files'] = 0;
			return false;
		}

		return true;
	}

	protected function unsetDisabledActions(array &$data): void
	{
		$disabledActions = array_flip($this->getDisabledActions());

		foreach (array_keys($data) as $action) {
			if (array_key_exists($action, $disabledActions))
				unset($data[$action]);
		}

		if (array_key_exists('search', $disabledActions))
			Utils::$context['allow_search'] = false;

		if (array_key_exists('moderate', $disabledActions))
			Utils::$context['allow_moderation_center'] = false;

		if (array_key_exists('calendar', $disabledActions))
			Utils::$context['allow_calendar'] = false;

		if (array_key_exists('mlist', $disabledActions))
			Utils::$context['allow_memberlist'] = false;

		Utils::$context['lp_disabled_actions'] = $disabledActions;
	}

	protected function redirectFromDisabledActions(): void
	{
		if (empty(Utils::$context['current_action']))
			return;

		if (array_key_exists(Utils::$context['current_action'], Utils::$context['lp_disabled_actions'])) {
			Utils::redirectexit();
		}
	}

	protected function getDisabledActions(): array
	{
		return [...Setting::getDisabledActions(), Action::HOME->value];
	}
}
