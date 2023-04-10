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
 * @version 2.1
 */

namespace Bugo\LightPortal\Partials;

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
				onServerSearch: async function (search, virtualSelect) {
					let response = await fetch("' . $this->context['canonical_url'] . ';members", {
						method: "POST",
						headers: {
							"Content-Type": "application/json; charset=utf-8"
						},
						body: JSON.stringify({
							search
						})
					});

					if (response.ok) {
						const json = await response.json();

						let data = [];
						for (let i = 0; i < json.length; i++) {
							data.push({label: json[i].text, value: json[i].value})
						}

						virtualSelect.setServerOptions(data)
					} else {
						virtualSelect.setServerOptions(false)
					}
				}
			});
		</script>';
	}
}
