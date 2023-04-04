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
		VirtualSelect.init({
			ele: "#included_boards",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			multiple: true,
			search: true,
			markSearchResults: true,
			placeholder: "', $txt['lp_ads_block']['included_boards_select'], '",
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '",
			allOptionsSelectedText: "', $txt['all'], '",
			showValueAsTags: true,
			maxWidth: "100%",
			options: [';

	foreach ($context['lp_selected_boards'] as $cat) {
		echo '
				{
					label: "', $cat['name'], '",
					options: [';

		foreach ($cat['boards'] as $id_board => $board) {
			echo '
						{label: "', $board['name'], '", value: "', $id_board, '"},';
		}

		echo '
					]
				},';
	}

	echo '
			],
			selectedValue: [', $context['lp_block']['options']['parameters']['included_boards'] ?? '', ']
		});
		VirtualSelect.init({
			ele: "#included_topics",', ($context['right_to_left'] ? '
			textDirection: "rtl",' : ''), '
			dropboxWrapper: "body",
			multiple: true,
			search: true,
			markSearchResults: true,
			showSelectedOptionsFirst: true,
			placeholder: "', $txt['lp_ads_block']['included_topics_select'], '",
			noSearchResultsText: "', $txt['no_matches'], '",
			searchPlaceholderText: "', $txt['search'], '",
			allOptionsSelectedText: "', $txt['all'], '",
			noOptionsText: "', $txt['lp_frontpage_topics_no_items'], '",
			moreText: "', $txt['post_options'], '",
			showValueAsTags: true,
			maxWidth: "100%",
			options: [';

	foreach ($context['lp_selected_topics'] as $id => $topic) {
		echo '
				{label: "', $topic, '", value: "', $id, '"},';
	}

	echo '
			],
			selectedValue: [', $context['lp_block']['options']['parameters']['included_topics'] ?? '', '],
			onServerSearch: async function (search, virtualSelect) {
				fetch("', $context['canonical_url'], ';topic_list", {
					method: "POST",
					headers: {
						"Content-Type": "application/json; charset=utf-8"
					},
					body: JSON.stringify({
						search
					})
				})
				.then(response => response.json())
				.then(function (json) {
					let data = [];
					for (let i = 0; i < json.length; i++) {
						data.push({label: json[i].subject, value: json[i].id})
					}

					virtualSelect.setServerOptions(data)
				})
				.catch(function (error) {
					virtualSelect.setServerOptions(false)
				})
			}
		});
	</script>';
}
