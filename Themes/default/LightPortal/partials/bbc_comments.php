<?php

global $txt;

echo '
	<script>
		const lpDisabledBbc = new TomSelect("#lp_disabled_bbc_in_comments", {
			plugins: {
				remove_button: {
					title: "', $txt['remove'], '",
				},
				clear_button: {
					title: "', $txt['remove_all'], '",
				},
				checkbox_options: {}
			},
			hideSelected: true,
		});

		function selectAllBbc(elem) {
			if (elem.checked) {
				let allTags = document.querySelectorAll("#lp_disabled_bbc_in_comments option");
				lpDisabledBbc.addOptions(Array.from(allTags).map(el => el.value));
				allTags.forEach(function (item) {
					lpDisabledBbc.addItem(item.value, true);
				});
				return;
			}

			lpDisabledBbc.clear();
		}
	</script>';
