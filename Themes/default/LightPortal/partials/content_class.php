<?php

global $context, $txt;

if (! empty($context['lp_block']['options']['no_content_class']))
	return;

echo '
	<script>
		VirtualSelect.init({
			ele: "#content_class",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			showSelectedOptionsFirst: true,
			optionHeight: "60px",
			placeholder: "', $txt['no'], '",
			options: [';

foreach ($context['lp_all_content_classes'] as $key => $template) {
	echo '
				{
					label: `' . sprintf($template, empty($key) ? $txt['no'] : $key, '') . '`,
					value: "' . $key . '"
				},';
}

echo '
			],';

if (! empty($context['lp_block']['content_class'])) {
	echo '
			selectedValue: "', $context['lp_block']['content_class'], '",';
}

echo '
			labelRenderer: function (data) {
				return `<div>${data.label}</div>`;
			}
		});
	</script>';
