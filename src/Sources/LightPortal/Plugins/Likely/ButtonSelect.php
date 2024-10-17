<?php declare(strict_types=1);

/**
 * @package Likely (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Plugins\Likely;

use Bugo\Compat\{Lang, Utils};
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
				$items[] = Utils::escapeJavaScript($button);
			}
		}

		return /** @lang text */ '
		<div id="buttons" name="buttons"></div>
		<script>
			VirtualSelect.init({
				ele: "#buttons",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . Lang::$txt['lp_likely']['select_buttons'] . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
