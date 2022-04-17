<?php

global $context, $txt;

if (empty($context['lp_all_title_classes']))
	return;

echo '
	<script>
		VirtualSelect.init({
			ele: "#title_class",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			showSelectedOptionsFirst: true,
			optionHeight: "60px",
			placeholder: "', $txt['no'], '",
			maxWidth: "100%",
			options: [';

foreach ($context['lp_all_title_classes'] as $key => $template) {
	echo '
				{
					label: `' . sprintf($template, empty($key) ? $txt['no'] : $key) . '`,
					value: "' . $key . '"
				},';
}

echo '
			],';

if (! empty($context['lp_block']['title_class'])) {
	echo '
			selectedValue: "', $context['lp_block']['title_class'], '",';
}

echo '
			labelRenderer: function (data) {
				return `<div>${data.label}</div>`;
			}
		});
	</script>';
