<?php declare(strict_types=1);

/**
 * @package FaBoardIcons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\FaBoardIcons;

use Bugo\Compat\Config;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::ARTICLE)]
class FaBoardIcons extends Plugin
{
	public function frontBoards(Event $e): void
	{
		if (! $this->isBaseModInstalled())
			return;

		$e->args->columns[] = 'b.fabi_icon, b.fabi_color';
	}

	public function frontBoardsRow(Event $e): void
	{
		if (! $this->isBaseModInstalled())
			return;

		$boards = &$e->args->articles;
		$row = $e->args->row;

		$icon = ! empty($row['fabi_icon']) && empty(Config::$modSettings['fabi_force_default_icon'])
			? $row['fabi_icon']
			: (empty(Config::$modSettings['fabi_default_icon']) ? 'fas fa-comments' : Config::$modSettings['fabi_default_icon']);

		$color = ! empty($row['fabi_color']) && empty(Config::$modSettings['fabi_force_default_color'])
			? $row['fabi_color']
			: (empty(Config::$modSettings['fabi_default_color']) ? '' : Config::$modSettings['fabi_default_color']);

		$boards[$row['id_board']]['title'] = Str::html('i')->class($icon . ' fa')
			->style(empty($color) ? null : 'color: ' . $color) . ' ' . $boards[$row['id_board']]['title'];
	}

	private function isBaseModInstalled(): bool
	{
		return is_file(Config::$sourcedir . '/FA-BoardIcons/FA-BoardIcons.php');
	}
}
