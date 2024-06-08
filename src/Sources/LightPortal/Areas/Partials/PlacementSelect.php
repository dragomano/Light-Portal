<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Lang, Utils};

use function func_get_args;
use function json_encode;

final class PlacementSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'placement';
		$params['value'] ??= Utils::$context['lp_block']['placement'];

		$data = [];
		foreach (Utils::$context['lp_block_placements'] as $level => $title) {
			$data[] = [
				'label' => $title,
				'value' => $level,
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",
				hideClearButton: true,' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				placeholder: "' . Lang::$txt['lp_block_placement_select'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: "' . $params['value'] . '"
			});
		</script>';
	}
}
