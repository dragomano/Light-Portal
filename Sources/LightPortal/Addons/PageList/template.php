<?php

function template_page_list_above() {}

function template_page_list_below()
{
	global $context;

	echo '
	<script>
		VirtualSelect.init({
			ele: "#sort",
			hideClearButton: true,' . ($context['right_to_left'] ? '
			textDirection: "rtl",' : '') . '
			dropboxWrapper: "body"
		});
	</script>';
}
