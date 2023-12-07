<?php declare(strict_types=1);

/**
 * PageAuthorSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Areas\Partials;

final class PageAuthorSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		return /** @lang text */ '
		<div id="page_author" name="page_author"></div>
		<script>
			VirtualSelect.init({
				ele: "#page_author",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				markSearchResults: true,
				placeholder: "' . $this->txt['search'] . '",
				noSearchResultsText: "' . $this->txt['lp_no_such_members'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				onServerSearch: function (search, virtualSelect) {
					return axios.post("' . $this->context['canonical_url'] . ';members", {
						search
					}).then(response => {
						const data = response.data
						const members = []

						for (let i = 0; i < data.length; i++) {
							members.push({ label: data[i].text, value: data[i].value })
						}

						virtualSelect.setServerOptions(members)
					}).catch(error => {
						console.error(error)

						virtualSelect.setServerOptions(false)
					})
				}
			});
		</script>';
	}
}
