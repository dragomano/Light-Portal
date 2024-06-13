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

namespace Bugo\LightPortal;

use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Utils\SMFHookTrait;

use function str_starts_with;

use const SMF_VERSION;

if (! defined('SMF'))
	die('No direct access...');

final class Integration
{
	use SMFHookTrait;

	public function __invoke(): void
	{
		if (str_starts_with(SMF_VERSION, '3.0')) {
			$this->applyHook(Hook::preLoad, Hooks\PreLoad::class);
			$this->applyHook(Hook::permissionsList, Hooks\PermissionsList::class);
		} else {
			$this->applyHook(Hook::userInfo, Hooks\UserInfo::class);
			$this->applyHook(Hook::loadIllegalGuestPermissions, Hooks\LoadIllegalGuestPermissions::class);
			$this->applyHook(Hook::loadPermissions, Hooks\LoadPermissions::class);
		}

		$this->applyHook(Hook::preCssOutput, Hooks\PreCssOutput::class);
		$this->applyHook(Hook::loadTheme, Hooks\LoadTheme::class);
		$this->applyHook(Hook::redirect, Hooks\Redirect::class);
		$this->applyHook(Hook::actions, Hooks\Actions::class);
		$this->applyHook(Hook::defaultAction, Hooks\DefaultAction::class);
		$this->applyHook(Hook::currentAction, Hooks\CurrentAction::class);
		$this->applyHook(Hook::menuButtons, Hooks\MenuButtons::class);
		$this->applyHook(Hook::displayButtons, Hooks\DisplayButtons::class);
		$this->applyHook(Hook::deleteMembers, Hooks\DeleteMembers::class);
		$this->applyHook(Hook::alertTypes, Hooks\AlertTypes::class);
		$this->applyHook(Hook::fetchAlerts, Hooks\FetchAlerts::class);
		$this->applyHook(Hook::profileAreas, Hooks\ProfileAreas::class);
		$this->applyHook(Hook::profilePopup, Hooks\ProfilePopup::class);
		$this->applyHook(Hook::downloadRequest, Hooks\DownloadRequest::class);
		$this->applyHook(Hook::whosOnline, Hooks\WhosOnline::class);
	}
}
