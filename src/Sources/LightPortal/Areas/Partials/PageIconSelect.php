<?php declare(strict_types=1);

/**
 * PageIconSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Lang, Utils};

final class PageIconSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$id = empty($params['id']) ? 'page_icon' : $params['id'];

		$icon = empty($params['icon']) ? (Utils::$context['lp_page']['options']['page_icon'] ?? '') : $params['icon'];

		$template = $this->getIcon($icon) . $icon;

		return /** @lang text */ '
		<div id="' . $id . '" name="' . $id . '"></div>
		<input
			type="checkbox"
			name="show_in_menu"
			id="show_in_menu"' . (Utils::$context['lp_page']['options']['show_in_menu'] ? ' checked=""' : '') . '
			class="checkbox"
		>
		<label class="label" for="show_in_menu" style="margin-left: 1em"></label>
		<script>
			VirtualSelect.init({
				ele: "#' . $id . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				allowNewOption: true,
				disabled: ' . (Utils::$context['lp_page']['options']['show_in_menu'] ? 'false' : 'true') . ',
				placeholder: "cheese",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				options: [
					{
						label: ' . Utils::escapeJavaScript($template) . ',
						value: "' . $icon . '"
					}
				],
				selectedValue: "' . $icon . '",
				labelRenderer: function (data) {
					return `<i class="${data.value} fa-fw"></i> ${data.value}`;
				},
				onServerSearch: async function (search, virtualSelect) {
					await axios.post("' . Utils::$context['form_action'] . ';icons", {
						search
					})
						.then(({ data }) => {
							const icons = [];
							for (let i = 0; i < data.length; i++) {
								icons.push({ label: data[i].innerHTML, value: data[i].value })
							}

							virtualSelect.setServerOptions(icons)
						})
						.catch(function (error) {
							virtualSelect.setServerOptions(false)
						})
				}
			});
			document.querySelector("#show_in_menu").addEventListener("change", function (e) {
				if (e.target.checked) {
					document.querySelector("#' . $id . '").enable();
				} else {
					document.querySelector("#' . $id . '").disable();
				}
			});
		</script>';
	}
}
