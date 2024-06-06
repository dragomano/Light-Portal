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

final class StatusSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$data = [];
		foreach (Lang::$txt['lp_page_status_set'] as $value => $label) {
			$data[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		return /** @lang text */ '
		<div id="status" name="status"></div>
		<script>
			VirtualSelect.init({
				ele: "#status",
				hideClearButton: true,' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				options: ' . json_encode($data) . ',
				selectedValue: ' . Utils::$context['lp_page']['status'] . '
			});
		</script>';
	}
}
