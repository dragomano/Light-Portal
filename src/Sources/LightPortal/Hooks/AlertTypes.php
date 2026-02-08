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

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use LightPortal\Enums\AlertAction;
use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

class AlertTypes
{
	private const YES = 'yes';

	private const NEVER = 'never';

	public function __invoke(array &$types): void
	{
		Lang::$txt['alert_group_light_portal'] = Lang::$txt['lp_portal'];

		if (Setting::getCommentBlock() === 'default') {
			$types['light_portal'] = [
				AlertAction::PAGE_COMMENT->name() => [
					'alert' => self::YES,
					'email' => self::NEVER,
					'permission' => [
						'name'     => 'light_portal_manage_pages_own',
						'is_board' => false,
					]
				],
				AlertAction::PAGE_COMMENT_REPLY->name() => [
					'alert' => self::YES,
					'email' => self::NEVER,
					'permission' => [
						'name'     => 'light_portal_view',
						'is_board' => false,
					]
				],
				AlertAction::PAGE_COMMENT_MENTION->name() => [
					'alert' => self::YES,
					'email' => self::NEVER,
					'permission' => [
						'name'     => 'light_portal_view',
						'is_board' => false,
					]
				],
			];
		}

		if (empty(Config::$modSettings['enable_mentions'])) {
			unset($types['light_portal'][AlertAction::PAGE_COMMENT_MENTION->name()]);
		}

		$types['light_portal'][AlertAction::PAGE_UNAPPROVED->name()] = [
			'alert' => self::YES,
			'email' => self::YES,
			'permission' => [
				'name'     => 'light_portal_manage_pages_any',
				'is_board' => false,
			]
		];
	}
}
