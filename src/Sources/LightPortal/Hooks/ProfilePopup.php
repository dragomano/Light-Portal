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

use Bugo\Compat\Lang;
use Bugo\Compat\User;

if (! defined('SMF'))
	die('No direct access...');

class ProfilePopup
{
	public function __invoke(array &$items): void
	{
		if (User::$me->is_admin || empty(User::$me->allowedTo('light_portal_manage_pages_own')))
			return;

		$counter = 0;
		foreach ($items as $item) {
			$counter++;

			if ($item['area'] === 'showdrafts')
				break;
		}

		$items = array_merge(
			array_slice($items, 0, $counter, true),
			[
				[
					'menu'  => 'info',
					'area'  => 'lp_my_pages',
					'title' => Lang::$txt['lp_my_pages']
				]
			],
			array_slice($items, $counter, null, true)
		);
	}
}
