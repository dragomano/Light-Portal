<?php declare(strict_types=1);

/**
 * ButtonSelect.php
 *
 * @package Likely (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\Likely;

use Bugo\LightPortal\Areas\Partials\AbstractPartial;

final class ButtonSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['data']  ??= [];
		$params['value'] ??= [];

		$data = $items = [];
		foreach ($params['data'] as $button) {
			$data[] = '{label: "' . $button . '", value: "' . $button . '"}';

			if (in_array($button, $params['value'])) {
				$items[] = $this->jsEscape($button);
			}
		}

		return /** @lang text */ '
		<div id="buttons" name="buttons"></div>
		<script>
			VirtualSelect.init({
				ele: "#buttons",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . $this->txt['lp_likely']['select_buttons'] . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
