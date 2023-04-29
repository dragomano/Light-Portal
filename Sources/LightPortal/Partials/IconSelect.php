<?php declare(strict_types=1);

/**
 * IconSelect.php
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

final class IconSelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$id = empty($params['id']) ? 'icon' : $params['id'];

		$icon = empty($params['icon']) ? ($this->context['lp_block']['icon'] ?? '') : $params['icon'];
		$type = empty($params['type']) ? ($this->context['lp_block']['type'] ?? '') : $params['type'];

		$template = $this->getIcon($icon) . $icon;

		return /** @lang text */ '
		<select id="' . $id . '" name="' . $id . '"></select>
		<script>
			new TomSelect("#' . $id . '", {
				plugins: {
					remove_button:{
						title: "' . $this->txt['remove'] . '",
					}
				},
				searchField: "value",
				allowEmptyOption: true,
				closeAfterSelect: false,
				placeholder: "cheese",' . (empty($icon) ? '' : '
				options: [
					{text: `' . $template . '`, value: "' . $icon . '"}
				],
				items: ["' . $icon . '"],') . '
				shouldLoad: function (search) {
					return search.length >= 3;
				},
				load: function (search, callback) {
					fetch("' . $this->context['canonical_url'] . ';icons", {
						method: "POST",
						headers: {
							"Content-Type": "application/json; charset=utf-8"
						},
						body: JSON.stringify({
							search,
							add_block: "' . $type . '"
						})
					})
					.then(response => response.json())
					.then(function (json) {
						let data = [];
						for (let i = 0; i < json.length; i++) {
							data.push({text: json[i].innerHTML, value: json[i].value})
						}

						callback(data)
					})
					.catch(function (error) {
						callback(false)
					})
				},
				render: {
					option: function (item, escape) {
						return `<div><i class="${item.value} fa-fw"></i>&nbsp;${item.value}</div>`;
					},
					item: function (item, escape) {
						return `<div><i class="${item.value} fa-fw"></i>&nbsp;${item.value}</div>`;
					},
					option_create: function(data, escape) {
						return `<div class="create">' . $this->txt['ban_add'] . ' <strong>` + escape(data.input) + `</strong>&hellip;</div>`;
					},
					no_results: function(data, escape) {
						return `<div class="no-results">' . $this->txt['no_matches'] . '</div>`;
					},
					not_loading: function(data, escape) {
						return `<div class="optgroup-header">' . sprintf($this->txt['lp_min_search_length'], 3) . '</div>`;
					}
				},
				create: function(input) {
					return {
						value: input.toLowerCase(),
						text: input.toLowerCase()
					}
				}
			});
		</script>';
	}
}
