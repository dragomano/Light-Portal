<?php

global $txt, $modSettings;

echo '
	<script>
		new TomSelect("#keywords", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			maxItems: ', $modSettings['lp_page_maximum_tags'] ?? 10, ',
			hideSelected: true,
			placeholder: "', $txt['lp_page_keywords_placeholder'], '",
			allowEmptyOption: true,
			closeAfterSelect: false,
			render: {
				option_create: function(data, escape) {
					return `<div class="create">', $txt['ban_add'], ' <strong>` + escape(data.input) + `</strong>&hellip;</div>`;
				},
				no_results: function() {
					return `<div class="no-results">', $txt['no_matches'], '</div>`;
				},
				not_loading: function(data, escape) {
					return `<div class="optgroup-header">', sprintf($txt['lp_min_search_length'], 3), '</div>`;
				}
			},
			create: function(input) {
				return {value: input, text: input}
			}
		});
	</script>';
