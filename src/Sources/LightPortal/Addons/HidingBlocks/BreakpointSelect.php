<?php declare(strict_types=1);

/**
 * BreakpointSelect.php
 *
 * @package HidingBlocks (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 01.05.23
 */

namespace Bugo\LightPortal\Addons\HidingBlocks;

use Bugo\LightPortal\Areas\Partials\AbstractPartial;

final class BreakpointSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$current_breakpoints = $this->context['lp_block']['options']['parameters']['hidden_breakpoints'] ?? [];
		$current_breakpoints = is_array($current_breakpoints) ? $current_breakpoints : explode(',', $current_breakpoints);

		$breakpoints = array_combine(['xs', 'sm', 'md', 'lg', 'xl'], $this->txt['lp_hiding_blocks']['hidden_breakpoints_set']);

		$data = $items = [];

		foreach ($breakpoints as $bp => $name) {
			$data[] = '{label: "' . $name . '", value: "' . $bp . '"}';

			if (in_array($bp, $current_breakpoints)) {
				$items[] = $this->jsEscape($bp);
			}
		}

		return /** @lang text */ '
		<div id="hidden_breakpoints" name="hidden_breakpoints"></div>
		<script>
			VirtualSelect.init({
				ele: "#hidden_breakpoints",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				showValueAsTags: true,
				placeholder: "' . $this->txt['lp_hiding_blocks']['hidden_breakpoints_subtext'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				selectAllText: "' . $this->txt['check_all'] . '",
				multiple: true,
				search: false,
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
