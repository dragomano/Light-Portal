<?php

global $context, $txt;

echo '
	<script>
		VirtualSelect.init({
			ele: "#category",
			hideClearButton: true,', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			search: true,
			markSearchResults: true,
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '"
		});
	</script>';
