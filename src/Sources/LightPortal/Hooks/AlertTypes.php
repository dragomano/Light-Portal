<?php declare(strict_types=1);

/**
 * AlertTypes.php
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

use Bugo\Compat\Lang;
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
				'page_comment' => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => [
						'name'     => 'light_portal_manage_pages_own',
						'is_board' => false,
					]
				],
				'page_comment_reply' => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => [
						'name'     => 'light_portal_view',
						'is_board' => false,
					]
				]
			];
		}

		$types['light_portal']['page_unapproved'] = [
			'alert' => 'yes',
			'email' => 'yes',
			'permission' => [
				'name'     => 'light_portal_manage_pages_any',
				'is_board' => false,
			]
		];
	}
}
