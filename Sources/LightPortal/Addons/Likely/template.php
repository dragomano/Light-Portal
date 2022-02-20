<?php

function template_likely_above() {}

function template_likely_below()
{
	global $context, $txt;

	$data = $items = [];
	foreach ($context['likely_buttons'] as $button) {
		$data[] = '{text: "' . $button . '", value: "' . $button . '"}';

		if (in_array($button, $context['lp_block']['options']['parameters']['buttons'])) {
			$items[] = JavaScriptEscape($button);
		}
	}

	echo '
	<script>
		new TomSelect("#buttons", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			options: [', implode(',', $data), '],
			items: [', implode(',', $items), '],
			hideSelected: true,
			placeholder: "', $txt['lp_likely']['select_buttons'], '",
			closeAfterSelect: false,
			render: {
				no_results: function() {
					return `<div class="no-results">', $txt['no_matches'], '</div>`;
				},
				not_loading: function(data, escape) {
					return `<div class="optgroup-header">', sprintf($txt['lp_min_search_length'], 3), '</div>`;
				}
			}
		});
	</script>';
}
