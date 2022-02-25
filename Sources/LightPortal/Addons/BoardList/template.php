<?php

function template_board_list_above() {}

function template_board_list_below()
{
	global $txt, $context;

	echo '
		<script>
			new TomSelect("#category_class", {
				plugins: {
					remove_button:{
						title: "', $txt['remove'], '",
					}
				},
				searchField: "value",
				options: [';

	$items = [];
	foreach ($context['category_classes'] as $key => $template) {
		echo '
					{
						text: `' . sprintf($template, empty($key) ? $txt['no'] : $key) . '`,
						value: "' . $key . '"
					},';

		if ($key == $context['lp_block']['options']['parameters']['category_class'])
			$items[] = $key ? JavaScriptEscape($key) : '';
	}

	echo '
				],
				items: [', implode(',', $items), '],
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
			new TomSelect("#board_class", {
				plugins: {
					remove_button:{
						title: "', $txt['remove'], '",
					}
				},
				searchField: "value",
				options: [';

	$items = [];
	foreach ($context['lp_all_content_classes'] as $key => $template) {
		echo '
					{
						text: `' . sprintf($template, empty($key) ? $txt['no'] : $key, '') . '`,
						value: "' . $key . '"
					},';

		if ($key == $context['lp_block']['options']['parameters']['board_class'])
			$items[] = $key ? JavaScriptEscape($key) : '';
	}

	echo '
				],
				items: [', implode(',', $items), '],
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
