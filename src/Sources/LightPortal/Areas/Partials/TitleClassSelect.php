<?php declare(strict_types=1);

/**
 * TitleClassSelect.php
 *
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

final class TitleClassSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'title_class';
		$params['data'] ??= Utils::$context['lp_all_title_classes'] ?? [];
		$params['value'] ??= Utils::$context['lp_block']['title_class'] ?? '';

		$data = [];
		foreach ($params['data'] as $key => $template) {
			$data[] = [
				'label' => sprintf($template, empty($key) ? Lang::$txt['no'] : $key),
				'value' => $key,
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				showSelectedOptionsFirst: true,
				optionHeight: "60px",
				placeholder: "' . Lang::$txt['no'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: "' . $params['value'] . '",
				labelRenderer: function (data) {
					return `<div>${data.label}</div>`;
				}
			});
		</script>';
	}
}
