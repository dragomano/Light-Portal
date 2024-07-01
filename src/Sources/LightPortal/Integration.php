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
use Bugo\LightPortal\Hooks\Actions;
use Bugo\LightPortal\Hooks\AlertTypes;
use Bugo\LightPortal\Hooks\CurrentAction;
use Bugo\LightPortal\Hooks\DefaultAction;
use Bugo\LightPortal\Hooks\DeleteMembers;
use Bugo\LightPortal\Hooks\DisplayButtons;
use Bugo\LightPortal\Hooks\DownloadRequest;
use Bugo\LightPortal\Hooks\FetchAlerts;
use Bugo\LightPortal\Hooks\LoadIllegalGuestPermissions;
use Bugo\LightPortal\Hooks\LoadPermissions;
use Bugo\LightPortal\Hooks\LoadTheme;
use Bugo\LightPortal\Hooks\MenuButtons;
use Bugo\LightPortal\Hooks\PermissionsList;
use Bugo\LightPortal\Hooks\PreCssOutput;
use Bugo\LightPortal\Hooks\PreLoad;
use Bugo\LightPortal\Hooks\ProfileAreas;
use Bugo\LightPortal\Hooks\ProfilePopup;
use Bugo\LightPortal\Hooks\Redirect;
use Bugo\LightPortal\Hooks\UserInfo;
use Bugo\LightPortal\Hooks\WhosOnline;
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
			$this->applyHook(Hook::preLoad, PreLoad::class);
			$this->applyHook(Hook::permissionsList, PermissionsList::class);
		} else {
			$this->applyHook(Hook::userInfo, UserInfo::class);
			$this->applyHook(Hook::loadIllegalGuestPermissions, LoadIllegalGuestPermissions::class);
			$this->applyHook(Hook::loadPermissions, LoadPermissions::class);
		}

		$this->applyHook(Hook::preCssOutput, PreCssOutput::class);
		$this->applyHook(Hook::loadTheme, LoadTheme::class);
		$this->applyHook(Hook::redirect, Redirect::class);
		$this->applyHook(Hook::actions, Actions::class);
		$this->applyHook(Hook::defaultAction, DefaultAction::class);
		$this->applyHook(Hook::currentAction, CurrentAction::class);
		$this->applyHook(Hook::menuButtons, MenuButtons::class);
		$this->applyHook(Hook::displayButtons, DisplayButtons::class);
		$this->applyHook(Hook::deleteMembers, DeleteMembers::class);
		$this->applyHook(Hook::alertTypes, AlertTypes::class);
		$this->applyHook(Hook::fetchAlerts, FetchAlerts::class);
		$this->applyHook(Hook::profileAreas, ProfileAreas::class);
		$this->applyHook(Hook::profilePopup, ProfilePopup::class);
		$this->applyHook(Hook::downloadRequest, DownloadRequest::class);
		$this->applyHook(Hook::whosOnline, WhosOnline::class);
	}
}
