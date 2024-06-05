<?php declare(strict_types=1);

/**
 * PermissionsList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Permissions;
use Bugo\Compat\Lang;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

class PermissionsList
{
	public function __invoke(array &$permissions): void
	{
		Lang::$txt['permissiongroup_light_portal'] = LP_NAME;

		Permissions::$permission_groups['global'][] = 'light_portal';
		Permissions::$left_permission_groups[] = 'light_portal';

		$permissions['light_portal_view'] = [
			'view_group' => 'light_portal',
			'scope'      => 'global',
		];

		$permissions['light_portal_manage_pages_own'] = [
			'generic_name' => 'light_portal_manage_pages',
			'own_any'      => 'own',
			'view_group'   => 'light_portal',
			'scope'        => 'global',
			'never_guests' => true,
		];

		$permissions['light_portal_manage_pages_any'] = [
			'generic_name' => 'light_portal_manage_pages',
			'own_any'      => 'any',
			'view_group'   => 'light_portal',
			'scope'        => 'global',
			'never_guests' => true,
		];

		$permissions['light_portal_approve_pages'] = [
			'view_group'   => 'light_portal',
			'scope'        => 'global',
			'never_guests' => true,
		];
	}
}
