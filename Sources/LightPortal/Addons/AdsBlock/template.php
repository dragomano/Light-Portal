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

function template_ads_placement_page_above()
{
	lp_show_blocks('page_top');
}

function template_ads_placement_page_below()
{
	lp_show_blocks('page_bottom');
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
		$data[] = '{label: "' . $title . '", value: "' . $position . '"}';

		if (in_array($position, $context['lp_block']['options']['parameters']['ads_placement'])) {
			$items[] = JavaScriptEscape($position);
		}
	}

	echo '
	<script>
		VirtualSelect.init({
			ele: "#ads_placement",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			maxWidth: "100%",
			showValueAsTags: true,
			search: false,
			multiple: true,
			placeholder: "', $txt['lp_ads_block']['select_placement'], '",
			clearButtonText: "', $txt['remove'], '",
			selectAllText: "', $txt['check_all'], '",
			options: [', implode(',', $data), '],
			selectedValue: [', implode(',', $items), ']
		});
	</script>';
}
