<?php declare(strict_types=1);

/**
 * PageIconSelect.php
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

final class PageIconSelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$id = empty($params['id']) ? 'page_icon' : $params['id'];

		$icon = empty($params['icon']) ? ($this->context['lp_page']['options']['page_icon'] ?? '') : $params['icon'];

		$template = $this->getIcon($icon) . $icon;

		return /** @lang text */ '
		<div id="' . $id . '" name="' . $id . '"></div>
		<input type="checkbox" name="show_in_menu" id="show_in_menu"' . ($this->context['lp_page']['options']['show_in_menu'] ? ' checked=""' : '') . ' class="checkbox">
		<label class="label" for="show_in_menu" style="margin-left: 1em"></label>
		<script defer>
			VirtualSelect.init({
				ele: "#' . $id . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				allowNewOption: true,
				disabled: ' . ($this->context['lp_page']['options']['show_in_menu'] ? 'false' : 'true') . ',
				placeholder: "cheese",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				options: [
					{
						label: ' . $this->jsEscape($template) . ',
						value: "' . $icon . '"
					}
				],
				selectedValue: "' . $icon . '",
				labelRenderer: function (data) {
					return `<i class="${data.value} fa-fw"></i> ${data.value}`;
				},
				onServerSearch: async function (search, virtualSelect) {
					fetch("' . $this->context['canonical_url'] . ';icons", {
						method: "POST",
						headers: {
							"Content-Type": "application/json; charset=utf-8"
						},
						body: JSON.stringify({
							search
						})
					})
					.then(response => response.json())
					.then(function (json) {
						let data = [];
						for (let i = 0; i < json.length; i++) {
							data.push({label: json[i].innerHTML, value: json[i].value})
						}

						virtualSelect.setServerOptions(data)
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
