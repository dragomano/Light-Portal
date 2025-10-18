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

namespace LightPortal\Enums;

use LightPortal\Renderers\RendererInterface;
use LightPortal\Utils\ParamWrapper;

enum PortalHook
{
	case addLayerAbove;
	case addLayerBelow;
	case addSettings;
	case afterPageContent;
	case beforePageContent;
	case changeIconSet;
	case commentButtons;
	case comments;
	case credits;
	case downloadRequest;
	case extendAdminAreas;
	case extendBasicConfig;
	case extendBlockAreas;
	case extendCategoryAreas;
	case extendPageAreas;
	case extendPluginAreas;
	case extendTagAreas;
	case findBlockErrors;
	case findPageErrors;
	case frontAssets;
	case frontBoards;
	case frontBoardsRow;
	case frontLayouts;
	case frontModes;
	case frontPages;
	case frontPagesRow;
	case frontTopics;
	case frontTopicsRow;
	case init;
	case layoutExtensions;
	case onBlockRemoving;
	case onBlockSaving;
	case onCustomPageImport;
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
	case validateBlockParams;
	case validatePageParams;

	public function createArgs(array $data = []): object
	{
		return match ($this) {
			self::addSettings,
			self::saveSettings => new class(...$data) {
				public function __construct(public array &$settings) {}
			},
			self::changeIconSet => new class(...$data) {
				public function __construct(public array &$set) {}
			},
			self::commentButtons => new class(...$data) {
				public function __construct(public readonly array $comment, public array &$buttons) {}
			},
			self::credits => new class(...$data) {
				public function __construct(public array &$links) {}
			},
			self::downloadRequest => new class(...$data) {
				public function __construct(public mixed &$attachRequest) {}
			},
			self::extendBasicConfig => new class(...$data) {
				public function __construct(public array &$configVars) {}
			},
			self::extendAdminAreas,
			self::extendBlockAreas,
			self::extendPageAreas,
			self::extendCategoryAreas,
			self::extendTagAreas,
			self::extendPluginAreas => new class(...$data) {
				public function __construct(public array &$areas) {}
			},
			self::findBlockErrors,
			self::findPageErrors => new class(...$data) {
				public function __construct(public array &$errors, public readonly array $data) {}
			},
			self::frontBoards,
			self::frontPages,
			self::frontTopics => new class(...$data) {
				public function __construct(
					public array &$columns,
					public array &$joins,
					public array &$params,
					public array &$wheres,
					public array &$orders
				) {}
			},
			self::frontBoardsRow,
			self::frontPagesRow,
			self::frontTopicsRow => new class(...$data) {
				public function __construct(public array &$articles, public readonly array $row) {}
			},
			self::frontLayouts => new class(...$data) {
				public function __construct(
					public RendererInterface &$renderer,
					public string &$layout,
					public array &$params
				) {}
			},
			self::frontModes => new class(...$data) {
				public function __construct(public array &$modes) {}
			},
			self::layoutExtensions => new class(...$data) {
				public function __construct(public array &$extensions) {}
			},
			self::onCustomPageImport => new class(...$data) {
				public function __construct(
					public array &$items,
					public array &$params,
					public array &$comments
				) {}
			},
			self::onBlockRemoving,
			self::onPageRemoving => new class(...$data) {
				public function __construct(public readonly array $items) {}
			},
			self::onBlockSaving,
			self::onPageSaving => new class(...$data) {
				public function __construct(public readonly int $item) {}
			},
			self::parseContent => new class(...$data) {
				public function __construct(public string &$content, public readonly string $type) {}
			},
			self::preloadStyles => new class(...$data) {
				public function __construct(public array $styles) {}
			},
			self::prepareAssets => new class(...$data) {
				public function __construct(public array &$assets) {}
			},
			self::prepareBlockFields,
			self::preparePageFields => new class(...$data) {
				public function __construct(public readonly array $options, public readonly string $type) {}
			},
			self::prepareBlockParams,
			self::validateBlockParams => new class(...$data) {
				public function __construct(
					public array &$baseParams,
					public array &$params,
					public readonly string $type
				) {}
			},
			self::prepareContent => new class(...$data) {
				public function __construct(
					public readonly string $type,
					public readonly int $id,
					public readonly int $cacheTime,
					public readonly ParamWrapper $parameters
				) {}
			},
			self::prepareEditor => new class(...$data) {
				public function __construct(public readonly array $object) {}
			},
			self::prepareIconList => new class(...$data) {
				public function __construct(public array &$icons, public string &$template) {}
			},
			self::prepareIconTemplate => new class(...$data) {
				public function __construct(public string &$template, public readonly string $icon) {}
			},
			self::preparePageData => new class(...$data) {
				public function __construct(public array &$data, public readonly bool $isAuthor) {}
			},
			self::preparePageParams,
			self::validatePageParams => new class(...$data) {
				public function __construct(public array &$params, public readonly string $type) {}
			},
			default => new class {},
		};
	}
}
