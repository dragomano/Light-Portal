<?php

echo '
	<script>
		const placementSelect = document.getElementById("placement");
		if (placementSelect.style.display !== "none") {
			new SlimSelect({
				select: placementSelect,
				showSearch: false,
				hideSelectedOption: true,
				closeOnSelect: true,
				showContent: "down"
			});
		}
	</script>';
