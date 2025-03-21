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

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Utils\Setting;

use function in_array;

if (! defined('SMF'))
	die('No direct access...');

class DisplayButtons
{
	public function __invoke(): void
	{
		if (empty(User::$me->is_admin) || Setting::isFrontpageMode('chosen_topics') === false)
			return;

		Utils::$context['normal_buttons']['lp_promote'] = [
			'text' => in_array(Utils::$context['current_topic'], Setting::getFrontpageTopics())
				? 'lp_remove_from_fp'
				: 'lp_promote_to_fp',
			'url'  => PortalSubAction::PROMOTE->url() . ';t=' . Utils::$context['current_topic']
		];
	}
}
