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

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\Status;

class PageStatusColumn extends StatusColumn
{
	public static function make(string $name = 'status', string $title = '', int $status = 0): static
	{
		return parent::make($name, $title)
			->setData(static function ($entry) use ($status) {
				$id = $entry['id'];

				if ($entry['status'] >= count(Status::cases()) - 1) {
					return __('lp_page_status_set')[$entry['status']] ?? __('no');
				}

				$isActive = $entry['status'] === $status ? 'true' : 'false';

				if (! User::$me->allowedTo('light_portal_approve_pages')) {
					return /**
						@lang text
					*/ '<span class="' . ($isActive === 'true' ? 'on' : 'off') . '" style="cursor: inherit;"></span>';
				}

				return /** @lang text */ '
					<div
						data-id="' . $id . '"
						data-signals="{ st_' . $id . ': ' . $isActive . ' }"
					>
						<span
							data-class:on="$st_' . $id . '"
							data-class:off="!$st_' . $id . '"
							data-attr:title="$st_' . $id . ' ? \'' . __('lp_action_off') . '\' : \'' . __('lp_action_on') . '\'"
							data-on:click="@post(\'' . (Utils::$context['lp_actions_url'] ?? '') . '\', {payload: {toggle_item: ' . $id . '}})"
						></span>
					</div>';
			}, 'centertext')
			->setSort('p.status DESC', 'p.status');
	}
}
