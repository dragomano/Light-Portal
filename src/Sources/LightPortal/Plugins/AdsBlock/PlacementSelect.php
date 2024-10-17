<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Areas\Partials\AbstractPartial;

final class PlacementSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		if (! is_array($params['value'])) {
			$params['value'] = explode(',', (string) $params['value']);
		}

		$data = $items = [];
		foreach ($params['data'] as $position => $title) {
			$data[] = '{label: "' . $title . '", value: "' . $position . '"}';

			if (in_array($position, $params['value'])) {
				$items[] = Utils::escapeJavaScript($position);
			}
		}

		return /** @lang text */ '
		<div id="ads_placement" name="ads_placement"></div>
		<script>
			VirtualSelect.init({
				ele: "#ads_placement",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				showValueAsTags: true,
				search: false,
				multiple: true,
				placeholder: "' . Lang::$txt['lp_block_placement_select'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				selectAllText: "' . Lang::$txt['check_all'] . '",
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
