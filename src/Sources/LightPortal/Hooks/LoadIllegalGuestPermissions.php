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

namespace LightPortal\Hooks;

use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

class LoadIllegalGuestPermissions
{
	public function __invoke(): void
	{
		Utils::$context['non_guest_permissions'] = array_merge(
			Utils::$context['non_guest_permissions'],
			[
				'light_portal_manage_pages_own',
				'light_portal_manage_pages_any',
				'light_portal_manage_pages',
				'light_portal_approve_pages',
			]
		);
	}
}
