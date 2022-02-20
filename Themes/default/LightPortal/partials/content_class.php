<?php

global $context, $txt;

if (empty($context['lp_block']['options']['no_content_class'])) {
	echo '
	<script>
		new TomSelect("#content_class", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			searchField: "value",
			options: [';

	foreach ($context['lp_all_content_classes'] as $key => $template) {
		echo '
				{
					text: `' . sprintf($template, empty($key) ? $txt['no'] : $key, '') . '`,
					value: "' . $key . '"
				},';
	}

	echo '
			],';

	if (! empty($context['lp_block']['content_class'])) {
		echo '
			items: ["', $context['lp_block']['content_class'], '"],';
	}

	echo '
			render: {
				option: function (item, escape) {
					return `<div>${item.text}</div>`;
				},
				item: function (item, escape) {
					return `<div>${item.text}</div>`;
				},
				no_results: function(data, escape) {
					return `<div class="no-results">', $txt['no_matches'], '</div>`;
				}
			}
		});
	</script>';
}
