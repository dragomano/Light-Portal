<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\FrontPageMode;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

class DisplayButtons
{
	public function __invoke(): void
	{
		if (empty(User::$me->is_admin) || Setting::isFrontpageMode(FrontPageMode::CHOSEN_TOPICS->value) === false)
			return;

		Utils::$context['normal_buttons']['lp_promote'] = [
			'text' => in_array(Utils::$context['current_topic'], Setting::getFrontpageTopics())
				? 'lp_remove_from_fp'
				: 'lp_promote_to_fp',
			'url'  => PortalSubAction::PROMOTE->url() . ';t=' . Utils::$context['current_topic']
		];
	}
}
