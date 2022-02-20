<?php

function template_language_access_above() {}

function template_language_access_below()
{
	global $context, $txt;

	// Prepare the language list
	$current_languages = $context['lp_block']['options']['parameters']['allowed_languages'] ?? [];
	$current_languages = is_array($current_languages) ? $current_languages : explode(',', $current_languages);

	$data = $items = [];

	foreach ($context['languages'] as $lang) {
		$data[] = '{text: "' . $lang['filename'] . '", value: "' . $lang['filename'] . '"}';

		if (in_array($lang['filename'], $current_languages)) {
			$items[] = JavaScriptEscape($lang['filename']);
		}
	}

	echo '
	<script>
		new TomSelect("#allowed_languages", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			options: [', implode(',', $data), '],
			items: [', implode(',', $items), '],
			hideSelected: true,
			placeholder: "', $txt['lp_language_access']['allowed_languages_subtext'], '",
			closeAfterSelect: false,
			render: {
				no_results: function() {
					return `<div class="no-results">', $txt['no_matches'], '</div>`;
				},
				not_loading: function(data, escape) {
					return `<div class="optgroup-header">', sprintf($txt['lp_min_search_length'], 3), '</div>`;
				}
			}
		});
	</script>';
}
