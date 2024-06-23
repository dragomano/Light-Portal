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

namespace Bugo\LightPortal\Enums;

enum PortalHook
{
	case addSettings;
	case afterPageContent;
	case beforePageContent;
	case changeIconSet;
	case commentButtons;
	case comments;
	case credits;
	case customLayoutExtensions;
	case downloadRequest;
	case extendBasicConfig;
	case findBlockErrors;
	case findPageErrors;
	case frontAssets;
	case frontBoards;
	case frontBoardsOutput;
	case frontLayouts;
	case frontModes;
	case frontPages;
	case frontPagesOutput;
	case frontTopics;
	case frontTopicsOutput;
	case importBlocks;
	case importCategories;
	case importPages;
	case init;
	case onBlockRemoving;
	case onBlockSaving;
	case onPageRemoving;
	case onPageSaving;
	case parseContent;
	case preloadStyles;
	case prepareAssets;
	case prepareBlockFields;
	case prepareBlockParams;
	case prepareContent;
	case prepareEditor;
	case prepareIconList;
	case prepareIconTemplate;
	case preparePageData;
	case preparePageFields;
	case preparePageParams;
	case saveSettings;
	case updateAdminAreas;
	case updateBlockAreas;
	case updateCategoryAreas;
	case updatePageAreas;
	case updatePluginAreas;
	case updateTagAreas;
	case validateBlockParams;
	case validatePageParams;
}
