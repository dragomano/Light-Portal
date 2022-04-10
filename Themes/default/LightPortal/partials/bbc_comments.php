<?php

global $context, $txt;

echo '
	<script>
		const lpDisabledBbc = VirtualSelect.init({
			ele: "#lp_disabled_bbc_in_comments",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			search: true,
			showValueAsTags: true,
			showSelectedOptionsFirst: true,
			placeholder: "', $txt['no'], '",
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '",
			clearButtonText: "', $txt['remove'], '"
		});

		function toggleSelectAll() {
			document.querySelector("#lp_disabled_bbc_in_comments").toggleSelectAll();
		}
	</script>';
