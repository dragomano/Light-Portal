<?php declare(strict_types=1);

/**
 * IconSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\LightPortal\Utils\{Lang, Utils};

final class IconSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$id = empty($params['id']) ? 'icon' : $params['id'];

		$icon = empty($params['icon']) ? (Utils::$context['lp_block']['icon'] ?? '') : $params['icon'];
		$type = empty($params['type']) ? (Utils::$context['lp_block']['type'] ?? '') : $params['type'];

		$template = $this->getIcon($icon) . $icon;

		return /** @lang text */ '
		<div id="' . $id . '" name="' . $id . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $id . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				allowNewOption: true,
				placeholder: "cheese",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
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
					await axios.post("' . Utils::$context['canonical_url'] . ';icons", {
						search,
						add_block: "' . $type . '"
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
		</script>';
	}
}
