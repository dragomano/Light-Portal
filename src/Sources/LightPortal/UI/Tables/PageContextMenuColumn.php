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
use Bugo\Compat\Utils;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Str;

use function LightPortal\app;

class PageContextMenuColumn extends ContextMenuColumn
{
	public static function make(string $name = 'actions', string $title = ''): static
	{
		$request   = app(RequestInterface::class);
		$isDeleted = $request->has('deleted');

		return parent::make($name, $title)
			->setData(static function ($entry) use ($isDeleted) {
				$id  = $entry['id'];
				$ctx = '$ctx_' . $id;
				$url = Utils::$context['lp_actions_url'] ?? '';

				$links = $isDeleted
					? Str::html('li')->addHtml(
						Link::make(__('restore_message'), [
							'data-on:click' => 'if (!confirm(smf_you_sure)) return; @post(\'' . $url .
								'\', {payload: {restore_item: ' . $id . '}}); ' . $ctx . ' = false',
						])
					) . Str::html('li')->addHtml(
						Link::make(__('lp_action_remove_permanently'), [
							'data-on:click' => 'if (!confirm(smf_you_sure)) return; @post(\'' . $url .
								'\', {payload: {remove_forever: ' . $id . '}}); ' . $ctx . ' = false',
						], 'button error')
					)
					: Str::html('li')->addHtml(
						Link::make(__('modify'), [
							'href' => Config::$scripturl . "?action=admin;area=lp_pages;sa=edit;id=$id",
						])
					) . Str::html('li')->addHtml(
						Link::make(__('remove'), [
							'data-on:click' => 'if (!confirm(smf_you_sure)) return; @post(\'' . $url .
								'\', {payload: {delete_item: ' . $id . '}}); ' . $ctx . ' = false',
						], 'button error')
					);

				return /** @lang text */ '
					<div data-id="' . $id . '" data-signals="{ ctx_' . $id . ': false }">
						<div class="context_menu" data-on:click__outside="' . $ctx . ' = false">
							' . IconButton::make('ellipsis', ['data-on:click' => $ctx . ' = true'], 'button floatnone') . '
							<div class="roundframe" data-show="' . $ctx . '">
								<ul>' . $links . '</ul>
							</div>
						</div>
					</div>';
			}, 'centertext');
	}
}
