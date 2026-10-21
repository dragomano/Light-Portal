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
use LightPortal\Enums\Status;

class StatusColumn extends Column
{
	public static function make(string $name = 'status', string $title = ''): static
	{
		return parent::make($name, $title ?: __('status'))
			->setStyle('width: 10%')
			->setData(static function ($entry) {
				$id     = $entry['id'];
				$status = $entry['status'] === Status::ACTIVE->value ? 'true' : 'false';

				return /** @lang text */ '
					<div
						data-id="' . $id . '"
						data-signals="{ st_' . $id . ': ' . $status . ' }"
					>
						<span
							data-class:on="$st_' . $id . '"
							data-class:off="!$st_' . $id . '"
							data-attr:title="$st_' . $id . ' ? \'' . __('lp_action_off') . '\' : \'' . __('lp_action_on') . '\'"
							data-on:click="@post(\'' . (Utils::$context['lp_actions_url'] ?? '') . '\', {payload: {toggle_item: ' . $id . '}})"
						></span>
					</div>';
			}, 'centertext')
			->setSort('status DESC', 'status');
	}
}
