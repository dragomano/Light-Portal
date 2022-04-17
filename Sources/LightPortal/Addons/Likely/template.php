<?php

function template_likely_above() {}

function template_likely_below()
{
	global $context, $txt;

	$data = $items = [];
	foreach ($context['likely_buttons'] as $button) {
		$data[] = '{label: "' . $button . '", value: "' . $button . '"}';

		if (in_array($button, $context['lp_block']['options']['parameters']['buttons'])) {
			$items[] = JavaScriptEscape($button);
		}
	}

	echo '
	<script>
		VirtualSelect.init({
			ele: "#buttons",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			maxWidth: "100%",
			multiple: true,
			search: true,
			markSearchResults: true,
			showValueAsTags: true,
			showSelectedOptionsFirst: true,
			placeholder: "', $txt['lp_likely']['select_buttons'], '",
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '",
			clearButtonText: "', $txt['remove'], '",
			options: [' . implode(',', $data) . '],
			selectedValue: [' . implode(',', $items) . ']
		});
	</script>';
}
