<?php

global $context;

addInlineJavaScript('
	const placementSelect = document.getElementById("placement");
	if (placementSelect.style.display !== "none") {
		VirtualSelect.init({
			ele: placementSelect,
			hideClearButton: true,' . ($context['right_to_left'] ? '
			textDirection: "rtl",' : '') . '
			dropboxWrapper: "body"
		});
	}', true);
