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
		$data[] = '{label: "' . $category['name'] . '", value: "' . $id . '"}';

		if (in_array($id, $current_categories)) {
			$items[] = JavaScriptEscape($id);
		}
	}

	echo '
	<script>
		VirtualSelect.init({
			ele: "#categories",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			multiple: true,
			search: true,
			markSearchResults: true,
			showValueAsTags: true,
			showSelectedOptionsFirst: true,
			placeholder: "', $txt['lp_page_list']['categories_subtext'], '",
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '",
			clearButtonText: "', $txt['remove'], '",
			options: [', implode(',', $data), '],
			selectedValue: [', implode(',', $items), '],
		});
		VirtualSelect.init({
			ele: "#sort",
			hideClearButton: true,' . ($context['right_to_left'] ? '
			textDirection: "rtl",' : '') . '
			dropboxWrapper: "body"
		});
	</script>';
}
