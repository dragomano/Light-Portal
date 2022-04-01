<?php

global $txt, $context;

echo '
	<script>
		new TomSelect("#icon", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			searchField: "value",
			allowEmptyOption: true,
			closeAfterSelect: false,
			placeholder: "cheese",';

if (! empty($context['lp_block']['icon'])) {
	echo '
			options: [
				{text: `', $context['lp_block']['icon_template'], '`, value: "', $context['lp_block']['icon'], '"}
			],
			items: ["', $context['lp_block']['icon'], '"],';
}

echo '
			shouldLoad: function (search) {
				return search.length >= 3;
			},
			load: function (search, callback) {
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
					return `<div class="create">', $txt['ban_add'], ' <strong>` + escape(data.input) + `</strong>&hellip;</div>`;
				},
				no_results: function(data, escape) {
					return `<div class="no-results">', $txt['no_matches'], '</div>`;
				},
				not_loading: function(data, escape) {
					return `<div class="optgroup-header">', sprintf($txt['lp_min_search_length'], 3), '</div>`;
				}
			},
			create: function(input) {
				return {value: input.toLowerCase(), text: input.toLowerCase()}
			}
		});
	</script>';
