<?php

global $context, $txt, $modSettings;

echo '
	<script>
		VirtualSelect.init({
			ele: "#keywords",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			multiple: true,
			search: true,
			markSearchResults: true,
			showValueAsTags: true,
			allowNewOption: true,
			showSelectedOptionsFirst: true,
			placeholder: "', $txt['lp_page_keywords_placeholder'], '",
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '",
			clearButtonText: "', $txt['remove'], '",
			maxValues: ', $modSettings['lp_page_maximum_keywords'] ?? 10, '
		});
	</script>';
