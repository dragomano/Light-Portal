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
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\AlertAction;
use Bugo\LightPortal\Utils\Str;

use function in_array;
use function strtr;

if (! defined('SMF'))
	die('No direct access...');

class FetchAlerts
{
	public function __invoke(array &$alerts): void
	{
		foreach ($alerts as $id => $alert) {
			if (in_array($alert['content_action'], AlertAction::names())) {
				$icon = $alert['content_action'] === AlertAction::PAGE_COMMENT->name() ? 'im_off' : 'im_on';
				$icon = $alert['content_action'] === AlertAction::PAGE_UNAPPROVED->name() ? 'news' : $icon;

				if ($alert['sender_id'] !== User::$me->id) {
					$alerts[$id]['icon'] = Str::html('span', ['class' => 'alert_icon main_icons ' . $icon]);
					$alerts[$id]['text'] = Lang::getTxt(
						'alert_' . $alert['content_type'] . '_' . $alert['content_action'],
						['gender' => $alert['extra']['sender_gender']]
					);

					$link = Config::$scripturl . '?action=profile;u=' . $alert['sender_id'];

					$substitutions = [
						'{member_link}' => $alert['sender_id'] && $alert['show_links']
							? Str::html('a')->href($link)->setText($alert['sender_name'])
							: Str::html('strong')->setText($alert['sender_name']),
						'{content_subject}' => '(' . $alert['extra']['content_subject'] . ')'
					];

					$alerts[$id]['text'] = strtr($alerts[$id]['text'], $substitutions);
					$alerts[$id]['target_href'] = $alert['extra']['content_link'];
				} else {
					unset($alerts[$id]);
				}
			}
		}
	}
}
