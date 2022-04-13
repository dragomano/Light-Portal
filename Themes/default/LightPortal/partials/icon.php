<?php

global $context, $txt;

echo '
	<script>
		VirtualSelect.init({
			ele: "#icon",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			search: true,
			placeholder: "', $txt['no'], '",
			noSearchResultsText: "' . $txt['no_matches'] . '",
			searchPlaceholderText: "' . $txt['search'] . '",
			onServerSearch: async function (search, virtualSelect) {
				fetch("', $context['canonical_url'], ';icons", {
					method: "POST",
					headers: {
						"Content-Type": "application/json; charset=utf-8"
					},
					body: JSON.stringify({
						search,
						add_block: "', $context['lp_block']['type'], '"
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
				.catch((error) => console.error(error))
			},';

if (! empty($context['lp_block']['icon'])) {
	echo '
			options: [
				{label: `', $context['lp_block']['icon_template'], '`, value: "', $context['lp_block']['icon'], '"}
			],
			selectedValue: "', $context['lp_block']['icon'], '",';
}

echo '
		});
	</script>';
