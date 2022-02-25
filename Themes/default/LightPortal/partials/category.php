<?php

global $txt;

echo '
	<script>
		new SlimSelect({
			select: "#category",
			hideSelectedOption: true,
			searchText: "', $txt['no_matches'], '",
			searchPlaceholder: "', $txt['search'], '",
			searchHighlight: true
		});
	</script>';
