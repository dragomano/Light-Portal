<?php

function template_article_list_above() {}

function template_article_list_below()
{
	global $context, $txt;

	echo '
		<script>
			VirtualSelect.init({
				ele: "#body_class",', ($context['right_to_left'] ? '
				textDirection: "rtl",' : ''), '
				dropboxWrapper: "body",
				showSelectedOptionsFirst: true,
				optionHeight: "60px",
				placeholder: "', $txt['no'], '",
				maxWidth: "100%",
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

	if (! empty($context['lp_block']['options']['parameters']['body_class'])) {
		echo '
				selectedValue: "', $context['lp_block']['options']['parameters']['body_class'], '",';
	}

	echo '
			});
		</script>';
}
