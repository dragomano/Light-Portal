<?php

function template_board_list_above() {}

function template_board_list_below()
{
	global $context, $txt;

	echo '
		<script>
			VirtualSelect.init({
				ele: "#category_class",', ($context['right_to_left'] ? '
				textDirection: "rtl",' : ''), '
				dropboxWrapper: "body",
				showSelectedOptionsFirst: true,
				optionHeight: "60px",
				placeholder: "', $txt['no'], '",
				options: [';

	foreach ($context['category_classes'] as $key => $template) {
		echo '
					{
						label: `' . sprintf($template, empty($key) ? $txt['no'] : $key) . '`,
						value: "' . $key . '"
					},';
	}

	echo '
				],';

	if (! empty($context['lp_block']['options']['parameters']['category_class'])) {
		echo '
				selectedValue: "', $context['lp_block']['options']['parameters']['category_class'], '",';
	}

	echo '
			});
			VirtualSelect.init({
				ele: "#board_class",', ($context['right_to_left'] ? '
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

	if (! empty($context['lp_block']['options']['parameters']['board_class'])) {
		echo '
				selectedValue: "', $context['lp_block']['options']['parameters']['board_class'], '",';
	}

	echo '
			});
		</script>';
}
