<?php declare(strict_types=1);

/**
 * PlacementSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Partials;

final class PlacementSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$data = [];
		foreach ($this->context['lp_block_placements'] as $level => $title) {
			$data[] = [
				'label' => $title,
				'value' => $level,
			];
		}

		return /** @lang text */ '
		<div id="placement" name="placement"></div>
		<script>
			VirtualSelect.init({
				ele: "#placement",
				hideClearButton: true,' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				options: ' . json_encode($data) . ',
				selectedValue: "' . $this->context['lp_block']['placement'] . '"
			});
		</script>';
	}
}
