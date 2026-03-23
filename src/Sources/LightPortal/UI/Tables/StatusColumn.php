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
use LightPortal\Enums\Status;

class StatusColumn extends Column
{
	public static function make(string $name = 'status', string $title = ''): static
	{
		return parent::make($name, $title ?: __('status'))
			->setStyle('width: 10%')
			->setData(static fn($entry) => /** @lang text */ '
				<div
					data-id="' . $entry['id'] . '"
					x-data="{ status: ' . ($entry['status'] === Status::ACTIVE->value ? 'true' : 'false') . ' }"
					x-init="$watch(\'status\', value => entity.toggleStatus($el))"
				>
					<span
						:class="{ \'on\': status, \'off\': !status }"
						:title="status ? \'' . __('lp_action_off') . '\' : \'' . __('lp_action_on') . '\'"
						@click.prevent="status = !status"
					></span>
				</div>', 'centertext')
			->setSort('status DESC', 'status');
	}
}
