<?php

function template_hiding_blocks_above() {}

function template_hiding_blocks_below()
{
	global $context, $txt;

	// Prepare the breakpoints list
	$current_breakpoints = $context['lp_block']['options']['parameters']['hidden_breakpoints'] ?? [];
	$current_breakpoints = is_array($current_breakpoints) ? $current_breakpoints : explode(',', $current_breakpoints);

	$breakpoints = array_combine(['xs', 'sm', 'md', 'lg', 'xl'], $txt['lp_hiding_blocks']['hidden_breakpoints_set']);

	$data = $items = [];
	foreach ($breakpoints as $bp => $name) {
		$data[] = '{text: "' . $name . '", value: "' . $bp . '"}';

		if (in_array($bp, $current_breakpoints)) {
			$items[] = JavaScriptEscape($bp);
		}
	}

	echo '
	<script>
		new TomSelect("#hidden_breakpoints", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			options: [', implode(',', $data), '],
			items: [', implode(',', $items), '],
			hideSelected: true,
			placeholder: "', $txt['lp_hiding_blocks']['hidden_breakpoints_subtext'], '",
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
