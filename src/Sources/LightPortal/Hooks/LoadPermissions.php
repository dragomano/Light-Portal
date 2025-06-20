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

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Lang;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

class LoadPermissions
{
	public function __invoke(array &$permissionGroups, array &$permissionList, array &$leftPermissionGroups): void
	{
		Lang::$txt['permissiongroup_light_portal'] = LP_NAME;

		$permissionList['membergroup']['light_portal_view']          = [false, 'light_portal'];
		$permissionList['membergroup']['light_portal_manage_pages']  = [true, 'light_portal'];
		$permissionList['membergroup']['light_portal_approve_pages'] = [false, 'light_portal'];

		$permissionGroups['membergroup'][] = $leftPermissionGroups[] = 'light_portal';
	}
}
