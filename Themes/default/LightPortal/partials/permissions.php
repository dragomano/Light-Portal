<?php

global $context;

if (empty($context['user']['is_admin']))
	return;

addInlineJavaScript('
	VirtualSelect.init({
		ele: "#permissions",
		hideClearButton: true,' . ($context['right_to_left'] ? '
		textDirection: "rtl",' : '') . '
		dropboxWrapper: "body"
	});', true);
