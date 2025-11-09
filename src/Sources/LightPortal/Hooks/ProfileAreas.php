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

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

class ProfileAreas
{
	use HasRequest;

	public function __invoke(array &$areas): void
	{
		if (Utils::$context['user']['is_admin'])
			return;

		$areas['info']['areas']['lp_my_pages'] = [
			'label'      => Lang::$txt['lp_my_pages'],
			'custom_url' => Config::$scripturl . '?action=admin;area=lp_pages',
			'icon'       => 'reports',
			'enabled'    => $this->request()->get('area') === 'popup',
			'permission' => [
				'own' => 'light_portal_manage_pages_own',
			],
		];
	}
}
