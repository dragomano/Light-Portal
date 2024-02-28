<?php declare(strict_types=1);

/**
 * SMFTraitNext.php (special for SMF 3.0)
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use SMF\Actions\Admin\Permissions;
use SMF\IntegrationHook;
use SMF\Lang;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @method getCamelName(string $name)
 */
trait SMFTraitNext
{
	public function permissionsList(array &$permissions): void
	{
		Lang::$txt['permissiongroup_light_portal'] = LP_NAME;

		Permissions::$permission_groups['global'][] = 'light_portal';
		Permissions::$left_permission_groups[] = 'light_portal';

		$permissions['light_portal_view'] = [
			'view_group' => 'light_portal',
			'scope' => 'global',
		];

		$permissions['light_portal_manage_pages_own'] = [
			'generic_name' => 'light_portal_manage_pages',
			'own_any' => 'own',
			'view_group' => 'light_portal',
			'scope' => 'global',
			'never_guests' => true,
		];

		$permissions['light_portal_manage_pages_any'] = [
			'generic_name' => 'light_portal_manage_pages',
			'own_any' => 'any',
			'view_group' => 'light_portal',
			'scope' => 'global',
			'never_guests' => true,
		];

		$permissions['light_portal_approve_pages'] = [
			'view_group' => 'light_portal',
			'scope' => 'global',
			'never_guests' => true,
		];
	}

	protected function applyHook(string $name, string $method = ''): void
	{
		$name = str_replace('integrate_', '', $name);

		if ($name === 'load_illegal_guest_permissions')
			return;

		if ($name === 'load_permissions') {
			$name = 'permissions_list';
		}

		if (func_num_args() === 1) {
			$method = lcfirst($this->getCamelName($name));
		}

		$method = static::class . '::' . str_replace('#', '', $method);

		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

		if ($name === 'init') {
			$name = 'pre_load';
		}

		IntegrationHook::add('integrate_' . $name, $method . '#', false, $file);
	}
}
