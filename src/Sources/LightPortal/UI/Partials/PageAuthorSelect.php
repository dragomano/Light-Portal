<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

final class PageAuthorSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		return /** @lang text */ '
		<div id="author_id" name="author_id"></div>
		<script>
			VirtualSelect.init({
				ele: "#author_id",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				markSearchResults: true,
				placeholder: "' . Lang::$txt['search'] . '",
				noSearchResultsText: "' . Lang::$txt['lp_no_such_members'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				onServerSearch: function (search, virtualSelect) {
					return axios.post("' . Utils::$context['form_action'] . ';members", {
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
