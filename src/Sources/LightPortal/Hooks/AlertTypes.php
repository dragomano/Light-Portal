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
use Bugo\LightPortal\Enums\AlertAction;
use Bugo\LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

class AlertTypes
{
	public function __invoke(array &$types): void
	{
		Lang::$txt['alert_group_light_portal'] = Lang::$txt['lp_portal'];

		if (Setting::getCommentBlock() === 'default') {
			$types['light_portal'] = [
				AlertAction::PAGE_COMMENT->name() => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => [
						'name'     => 'light_portal_manage_pages_own',
						'is_board' => false,
					]
				],
				AlertAction::PAGE_COMMENT_REPLY->name() => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => [
						'name'     => 'light_portal_view',
						'is_board' => false,
					]
				]
			];
		}

		$types['light_portal'][AlertAction::PAGE_UNAPPROVED->name()] = [
			'alert' => 'yes',
			'email' => 'yes',
			'permission' => [
				'name'     => 'light_portal_manage_pages_any',
				'is_board' => false,
			]
		];
	}
}
