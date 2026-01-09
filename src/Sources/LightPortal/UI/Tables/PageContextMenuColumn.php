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

namespace LightPortal\UI\Tables;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Str;

use function LightPortal\app;

class PageContextMenuColumn extends ContextMenuColumn
{
	public static function make(string $name = 'actions', string $title = ''): static
	{
		$request = app(RequestInterface::class);

		return parent::make($name, $title)
			->setData(fn($entry) => /** @lang text */ '
				<div data-id="' . $entry['id'] . '" x-data="{ showContextMenu: false }">
					<div class="context_menu" @click.outside="showContextMenu = false">
						' . IconButton::make('ellipsis', ['x-on:click.prevent' => 'showContextMenu = true'], 'button floatnone') . '
						<div class="roundframe" x-show="showContextMenu" x-transition.duration.500ms>
							<ul>' . (
								$request->has('deleted') ? (
									Str::html('li')->addHtml(
										Link::make(Lang::$txt['restore_message'], ['x-on:click.prevent' => 'showContextMenu = false; entity.restore($root)'])
									) .
									Str::html('li')->addHtml(
										Link::make(Lang::$txt['lp_action_remove_permanently'], ['x-on:click.prevent' => 'showContextMenu = false; entity.removeForever($root)'], 'button error')
									)
								) : (
									Str::html('li')->addHtml(
										Link::make(Lang::$txt['modify'], ['href' => Config::$scripturl . "?action=admin;area=lp_pages;sa=edit;id={$entry['id']}"])
									) .
									Str::html('li')->addHtml(
										Link::make(Lang::$txt['remove'], ['x-on:click.prevent' => 'showContextMenu = false; entity.remove($root)'], 'button error')
									)
								)
							) . '
							</ul>
						</div>
					</div>
				</div>', 'centertext'
			);
	}
}
