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

use LightPortal\Enums\ForumHook;
use LightPortal\Utils\Traits\HasForumHooks;

use const SMF_VERSION;

if (! defined('SMF'))
	die('No direct access...');

final class Integration
{
	use HasForumHooks;

	public function __invoke(): void
	{
		if (str_starts_with(SMF_VERSION, '3.0')) {
			$this->applyHook(ForumHook::preLoad, Init::class);
			$this->applyHook(ForumHook::permissionsList, PermissionsList::class);
			$this->applyHook(ForumHook::buildRoute, BuildRoute::class);
			$this->applyHook(ForumHook::routeParsers, RouteParsers::class);
		} else {
			$this->applyHook(ForumHook::userInfo, Init::class);
			$this->applyHook(ForumHook::loadIllegalGuestPermissions, LoadIllegalGuestPermissions::class);
			$this->applyHook(ForumHook::loadPermissions, LoadPermissions::class);
		}

		$this->applyHook(ForumHook::preCssOutput, PreCssOutput::class);
		$this->applyHook(ForumHook::loadTheme, LoadTheme::class);
		$this->applyHook(ForumHook::redirect, Redirect::class);
		$this->applyHook(ForumHook::actions, Actions::class);
		$this->applyHook(ForumHook::defaultAction, DefaultAction::class);
		$this->applyHook(ForumHook::currentAction, CurrentAction::class);
		$this->applyHook(ForumHook::menuButtons, MenuButtons::class);
		$this->applyHook(ForumHook::displayButtons, DisplayButtons::class);
		$this->applyHook(ForumHook::adminAreas, AdminAreas::class);
		$this->applyHook(ForumHook::helpadmin, HelpAdmin::class);
		$this->applyHook(ForumHook::deleteMembers, DeleteMembers::class);
		$this->applyHook(ForumHook::alertTypes, AlertTypes::class);
		$this->applyHook(ForumHook::fetchAlerts, FetchAlerts::class);
		$this->applyHook(ForumHook::profileAreas, ProfileAreas::class);
		$this->applyHook(ForumHook::profilePopup, ProfilePopup::class);
		$this->applyHook(ForumHook::downloadRequest, DownloadRequest::class);
		$this->applyHook(ForumHook::whosOnline, WhosOnline::class);
		$this->applyHook(ForumHook::credits, Credits::class);
	}
}
