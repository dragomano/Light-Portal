<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Utils;

interface SettingInterface
{
	public static function get(
		string $key,
		string $type = 'string',
		mixed $default = null,
		string $from = 'string'
	): mixed;

	public static function isFrontpage(string $slug): bool;

	public static function isFrontpageMode(string $mode): bool;

	public static function isStandaloneMode(): bool;

	public static function getCommentBlock(): string;

	public static function showRelatedPages(): bool;

	public static function getEnabledPlugins(): array;

	public static function getFrontpagePages(): array;

	public static function getFrontpageTopics(): array;

	public static function getHeaderPanelWidth(): int;

	public static function getFooterPanelWidth(): int;

	public static function getLeftPanelWidth(): array;

	public static function getRightPanelWidth(): array;

	public static function getPanelDirection(string $panel): string;

	public static function isSwapLeftRight(): bool;

	public static function hideBlocksInACP(): bool;

	public static function getDisabledActions(): array;

	public static function canMention(): bool;
}
