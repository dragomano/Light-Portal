<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

class ContextMenuColumn extends Column
{
	public static function make(string $name = 'actions', string $title = ''): static
	{
		return parent::make($name, $title ?: Lang::$txt['lp_actions'])
			->setStyle('width: 8%')
			->setData(static fn($entry) => /** @lang text */ '
				<div data-id="' . $entry['id'] . '" x-data="{ showContextMenu: false }">
					<div class="context_menu" @click.outside="showContextMenu = false">
						' . IconButton::make('ellipsis', ['x-on:click.prevent' => 'showContextMenu = true'], 'button floatnone') . '
						<div class="roundframe" x-show="showContextMenu" x-transition.duration.500ms>
							<ul>
								<li>' . LinkButton::make(Lang::$txt['modify'], ['href' => Utils::$context['form_action'] . ";sa=edit;id={$entry['id']}"]) . '</li>
								<li>' . LinkButton::make(Lang::$txt['remove'], ['x-on:click.prevent' => 'showContextMenu = false; entity.remove($root)'], 'button error') . '</li>
							</ul>
						</div>
					</div>
				</div>', 'centertext');
	}
}
