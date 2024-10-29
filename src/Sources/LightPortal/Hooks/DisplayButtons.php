<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Setting;

use function in_array;

use const LP_BASE_URL;

if (! defined('SMF'))
	die('No direct access...');

class DisplayButtons
{
	public function __invoke(): void
	{
		if (empty(User::$info['is_admin']) || Setting::isFrontpageMode('chosen_topics') === false)
			return;

		Utils::$context['normal_buttons']['lp_promote'] = [
			'text' => in_array(Utils::$context['current_topic'], Setting::getFrontpageTopics())
				? 'lp_remove_from_fp'
				: 'lp_promote_to_fp',
			'url'  => LP_BASE_URL . ';sa=promote;t=' . Utils::$context['current_topic']
		];
	}
}
