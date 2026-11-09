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

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Utils;

class ContextMenuColumn extends Column
{
	public static function make(string $name = 'actions', string $title = ''): static
	{
		return parent::make($name, $title ?: __('lp_actions'))
			->setStyle('width: 8%')
			->setData(static function ($entry) {
				$id  = $entry['id'];
				$ctx = '$ctx_' . $id;

				return /** @lang text */ '
					<div data-id="' . $id . '" data-signals="{ ctx_' . $id . ': false }">
						<div class="context_menu" data-on:click__outside="' . $ctx . ' = false">
							' . IconButton::make('ellipsis', ['data-on:click' => $ctx . ' = true'], 'button floatnone') . '
							<div class="roundframe" data-show="' . $ctx . '">
								<ul>
									<li>' . Link::make(__('modify'), ['href' => Utils::$context['form_action'] . ";sa=edit;id=$id"]) . '</li>
									<li>' . Link::make(__('remove'), [
										'data-on:click' => 'if (!confirm(smf_you_sure)) return; @post(\'' .
											(Utils::$context['lp_actions_url'] ?? '') . '\', {payload: {delete_item: ' . $id . '}}); ' .
											$ctx . ' = false',
									], 'button error') . '</li>
								</ul>
							</div>
						</div>
					</div>';
			}, 'centertext');
	}
}
