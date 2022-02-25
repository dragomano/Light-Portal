<?php

function template_ads_placement_board_above()
{
	lp_show_blocks('board_top');
}

function template_ads_placement_board_below()
{
	lp_show_blocks('board_bottom');
}

function template_ads_placement_topic_above()
{
	lp_show_blocks('topic_top');
}

function template_ads_placement_topic_below()
{
	lp_show_blocks('topic_bottom');
}

function template_ads_block_form_above() {}

function template_ads_block_form_below()
{
	global $scripturl, $context;

	echo '
	<form name="ads_block_form" action="', $scripturl, '?action=admin;area=lp_blocks;sa=add" method="post" accept-charset="', $context['character_set'], '" style="display: none">
		<input type="hidden" name="add_block" value="ads_block">
		<input type="hidden" name="placement" value="ads">
	</form>
	<script>
		const addButton = document.querySelector(\'h3 a[href$="placement=ads"]\');
		if (addButton) {
			addButton.removeAttribute("href");
			addButton.addEventListener("click", () => document.forms.ads_block_form.submit());
		}
	</script>';
}

function template_ads_block_above() {}

function template_ads_block_below()
{
	global $context, $txt;

	if (! is_array($context['lp_block']['options']['parameters']['ads_placement'])) {
		$context['lp_block']['options']['parameters']['ads_placement'] = explode(',', $context['lp_block']['options']['parameters']['ads_placement']);
	}

	$data = $items = [];

	foreach ($context['ads_placements'] as $position => $title) {
		$data[] = '{text: "' . $title . '", value: "' . $position . '"}';

		if (in_array($position, $context['lp_block']['options']['parameters']['ads_placement'])) {
			$items[] = JavaScriptEscape($position);
		}
	}

	echo '
	<script>
		new TomSelect("#ads_placement", {
			plugins: {
				remove_button:{
					title: "', $txt['remove'], '",
				}
			},
			options: [', implode(',', $data), '],
			items: [', implode(',', $items), '],
			hideSelected: true,
			placeholder: "', $txt['lp_ads_block']['select_placement'], '",
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
