<?php

global $context;

if ($context['user']['is_admin']) {
	echo '
	<script>
		new SlimSelect({
			select: "#permissions",
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});
	</script>';
}
