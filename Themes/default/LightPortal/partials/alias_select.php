<?php

global $context, $txt, $modSettings, $scripturl;

echo '
	<script>
		let frontpageAlias = document.getElementById("lp_frontpage_alias");
		if (frontpageAlias) {
			VirtualSelect.init({
				ele: frontpageAlias,', ($context['right_to_left'] ? '
				textDirection: "rtl",' : ''), '
				dropboxWrapper: "body",
				search: true,
				placeholder: "', $txt['no'], '",
				noSearchResultsText: "' . $txt['no_matches'] . '",
				searchPlaceholderText: "' . $txt['search'] . '",';

echo '
				onServerSearch: async function (search, virtualSelect) {
					fetch("', $scripturl, '?action=admin;area=lp_settings;sa=basic;alias_list", {
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
							data.push({label: json[i].value, value: json[i].value})
						}

						virtualSelect.setServerOptions(data)
					})
					.catch(function (error) {
						virtualSelect.setServerOptions(false)
					})
				},';

echo '
			});';

if (! empty($modSettings['lp_frontpage_alias'])) {
	$alias = JavaScriptEscape($modSettings['lp_frontpage_alias']);

	echo '
			document.getElementById("lp_frontpage_alias").setOptions([{label: ', $alias, ', value: ', $alias, '}]);
			document.getElementById("lp_frontpage_alias").setValue(', $alias, ');';
}

echo '
		}
	</script>';
