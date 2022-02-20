<?php

function template_page_list_above() {}

function template_page_list_below()
{
	global $context, $txt;

	// Prepare the category list
	$current_categories = $context['lp_block']['options']['parameters']['categories'] ?? [];
	$current_categories = is_array($current_categories) ? $current_categories : explode(',', $current_categories);

	$data = $items = [];
	foreach ($context['all_categories'] as $id => $category) {
		$data[] = '{text: "' . $category['name'] . '", value: "' . $id . '"}';

		if (in_array($id, $current_categories)) {
			$items[] = JavaScriptEscape($id);
		}
	}

	echo '
	<script>
		new TomSelect("#categories", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			options: [', implode(',', $data), '],
			items: [', implode(',', $items), '],
			hideSelected: true,
			placeholder: "', $txt['lp_page_list']['categories_subtext'], '",
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
