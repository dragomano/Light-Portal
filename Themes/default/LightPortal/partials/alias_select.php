<?php

global $txt, $scripturl, $modSettings;

echo '
	<script>
		let frontpageAlias = document.getElementById("lp_frontpage_alias");
		if (frontpageAlias) {
			let aliasSelect = new TomSelect(frontpageAlias, {
				hideSelected: true,
				searchField: ["value"],
				shouldLoad: function (search) {
					return search.length >= 3;
				},
				load: function (search, callback) {
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
							data.push({text: json[i].value, value: json[i].value})
						}

						callback(data)
					})
					.catch(function (error) {
						callback(false)
					})
				},
				render: {
					no_results: function(data, escape) {
						return `<div class="no-results">', $txt['no_matches'], '</div>`;
					},
					not_loading: function(data, escape) {
						return `<div class="optgroup-header">', sprintf($txt['lp_min_search_length'], 3), '</div>`;
					}
				},
			});';

if (! empty($modSettings['lp_frontpage_alias'])) {
	$alias = JavaScriptEscape($modSettings['lp_frontpage_alias']);

	echo '
			aliasSelect.addOption({value: ', $alias, ', text: ', $alias, '});
			aliasSelect.addItem(', $alias, ', true);';
}

echo '
		}
	</script>';
