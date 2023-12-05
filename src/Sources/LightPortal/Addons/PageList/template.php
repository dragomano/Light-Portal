<?php

function page_list_select(): string
{
	global $context;

	return '
	<script>
		VirtualSelect.init({
			ele: "#sort",
			hideClearButton: true,' . ($context['right_to_left'] ? '
			textDirection: "rtl",' : '') . '
			dropboxWrapper: "body"
		});
	</script>';
}
