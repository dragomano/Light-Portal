<?php

global $context, $txt;

if (empty($context['user']['is_admin']))
	return;

echo '
	<script>
		VirtualSelect.init({
			ele: "#page_author",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			search: true,
			markSearchResults: true,
			placeholder: "', $txt['search'], '",
			noSearchResultsText: "', $txt['lp_no_such_members'], '",
			searchPlaceholderText: "', $txt['search'], '",
			onServerSearch: async function (search, virtualSelect) {
				let response = await fetch("', $context['canonical_url'], ';members", {
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
