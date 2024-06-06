<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Utils\Str;

enum Hook
{
	case actions;
	case adminAreas;
	case alertTypes;
	case cleanCache;
	case credits;
	case currentAction;
	case defaultAction;
	case deleteMembers;
	case displayButtons;
	case downloadRequest;
	case fetchAlerts;
	case helpadmin;
	case loadIllegalGuestPermissions;
	case loadPermissions;
	case loadTheme;
	case manageThemes;
	case memberContext;
	case menuButtons;
	case messageindexButtons;
	case permissionsList;
	case preCssOutput;
	case preLoad;
	case prepareDisplayContext;
	case profileAreas;
	case profilePopup;
	case redirect;
	case repairAttachmentsNomsg;
	case simpleActions;
	case themeContext;
	case userInfo;
	case whosOnline;

	public function name(): string
	{
		return 'integrate_' . Str::getSnakeName($this->name);
	}
}
