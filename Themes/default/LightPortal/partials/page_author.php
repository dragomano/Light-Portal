<?php

global $context, $txt;

if ($context['user']['is_admin']) {
	echo '
	<script>
		new SlimSelect({
			select: "#page_author",
			allowDeselect: true,
			deselectLabel: "<span class=\"red\">✖</span>",
			ajax: async function (search, callback) {
				if (search.length < 3) {
					callback("', sprintf($txt['lp_min_search_length'], 3), '");
					return
				}

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
						data.push({text: json[i].text, value: json[i].value})
					}

					callback(data)
				} else {
					callback(false)
				}
			},
			hideSelectedOption: true,
			placeholder: "', $txt['lp_page_author_placeholder'], '",
			searchingText: "', $txt['search'], '...",
			searchText: "', $txt['no_matches'], '",
			searchPlaceholder: "', $txt['search'], '",
			searchHighlight: true
		});
	</script>';
}
