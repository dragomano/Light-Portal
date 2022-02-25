<?php

global $context, $txt;

if (! empty($context['lp_all_title_classes'])) {
	echo '
	<script>
		new TomSelect("#title_class", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			searchField: "value",
			options: [';

	foreach ($context['lp_all_title_classes'] as $key => $template) {
		echo '
				{
					text: `' . sprintf($template, empty($key) ? $txt['no'] : $key) . '`,
					value: "' . $key . '"
				},';
	}

	echo '
			],';

	if (! empty($context['lp_block']['title_class'])) {
		echo '
			items: ["', $context['lp_block']['title_class'], '"],';
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
