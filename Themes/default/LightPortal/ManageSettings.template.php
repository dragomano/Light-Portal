<?php

function template_callback_frontpage_mode_settings()
{
	global $modSettings, $txt, $context, $scripturl, $settings;

	echo '
	</dl>
	<dl class="settings" style="margin-top: -2em" x-data="{ frontpage_mode: \'', $modSettings['lp_frontpage_mode'] ?? 0, '\' }">
		<dt>
			<a id="setting_lp_frontpage_mode"></a> <span><label for="lp_frontpage_mode">', $txt['lp_frontpage_mode'], '</label></span>
		</dt>
		<dd>
			<select
				name="lp_frontpage_mode"
				id="lp_frontpage_mode"
				@change="frontpage_mode = $event.target.value; $dispatch(\'change-mode\', {front: frontpage_mode})"
			>';

	foreach ($context['lp_frontpage_modes'] as $mode => $label) {
		echo '
				<option value="', $mode, '"', ! empty($modSettings['lp_frontpage_mode']) && $mode === $modSettings['lp_frontpage_mode'] ? ' selected' : '', '>', $label, '</option>';
	}

	echo '
			</select>
		</dd>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_frontpage_title"></a> <span><label for="lp_frontpage_title">', $txt['lp_frontpage_title'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input
					type="text"
					name="lp_frontpage_title"
					id="lp_frontpage_title"
					value="', $modSettings['lp_frontpage_title'] ?? '', '"
					size="80"
					placeholder="', str_replace(array("'", "\""), "", $context['forum_name']), ' - ', $txt['lp_portal'], '"
				>
			</dd>
		</template>

		<template x-if="frontpage_mode === \'chosen_page\'">
			<dt>
				<a id="setting_lp_frontpage_alias"></a> <span><label for="lp_frontpage_alias">', $txt['lp_frontpage_alias'], '</label></span>
			</dt>
		</template>
		<template x-if="frontpage_mode === \'chosen_page\'">
			<dd>', $context['lp_frontpage_alias_select'], '</dd>
		</template>

		<template x-if="frontpage_mode === \'all_pages\'">
			<dt>
				<a id="setting_lp_frontpage_categories"></a>
				<span><label for="lp_frontpage_categories">', $txt['lp_frontpage_categories'], '</label></span>
			</dt>
		</template>
		<template x-if="frontpage_mode === \'all_pages\'">
			<dd>', $context['lp_frontpage_categories_select'], '</dd>
		</template>

		<template x-if="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_frontpage_boards"></a>
				<span><label for="lp_frontpage_boards">', $txt['lp_frontpage_boards'], '</label></span>
			</dt>
		</template>
		<template x-if="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">
			<dd>', $context['lp_frontpage_boards_select'], '</dd>
		</template>

		<template x-if="frontpage_mode === \'chosen_pages\'">
			<dt>
				<a id="setting_lp_frontpage_pages"></a> <span><label for="lp_frontpage_pages">', $txt['lp_frontpage_pages'], '</label>
			</dt>
		</template>
		<template x-if="frontpage_mode === \'chosen_pages\'">
			<dd>', $context['lp_frontpage_pages_select'], '</dd>
		</template>

		<template x-if="frontpage_mode === \'chosen_topics\'">
			<dt>
				<a id="setting_lp_frontpage_topics"></a> <span><label for="lp_frontpage_topics">', $txt['lp_frontpage_topics'], '</label>
			</dt>
		</template>
		<template x-if="frontpage_mode === \'chosen_topics\'">
			<dd>', $context['lp_frontpage_topics_select'], '</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_show_images_in_articles_help" href="', $scripturl, '?action=helpadmin;help=lp_show_images_in_articles_help" onclick="return reqOverlayDiv(this.href);">
					<span class="main_icons help" title="', $txt['help'], '"></span>
				</a>
				<a id="setting_lp_show_images_in_articles"></a> <span><label for="lp_show_images_in_articles">', $txt['lp_show_images_in_articles'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_show_images_in_articles" id="lp_show_images_in_articles"', empty($modSettings['lp_show_images_in_articles']) ? '' : ' checked', ' value="1">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_image_placeholder"></a> <span><label for="lp_image_placeholder">', $txt['lp_image_placeholder'], '</label></span><br><span class="smalltext">', $txt['lp_image_placeholder_subtext'], '</span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="text" name="lp_image_placeholder" id="lp_image_placeholder" value="', $modSettings['lp_image_placeholder'] ?? '', '" size="80" placeholder="', $txt['lp_example'], $settings['default_images_url'], '/smflogo.svg">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_show_teaser"></a> <span><label for="lp_show_teaser">', $txt['lp_show_teaser'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_show_teaser" id="lp_show_teaser"', empty($modSettings['lp_show_teaser']) ? '' : ' checked', ' value="1">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_show_author_help" href="', $scripturl, '?action=helpadmin;help=lp_show_author_help" onclick="return reqOverlayDiv(this.href);">
					<span class="main_icons help" title="', $txt['help'], '"></span>
				</a>
				<a id="setting_lp_show_author"></a> <span><label for="lp_show_author">', $txt['lp_show_author'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_show_author" id="lp_show_author"', empty($modSettings['lp_show_author']) ? '' : ' checked', ' value="1">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_show_views_and_comments"></a> <span><label for="lp_show_views_and_comments">', $txt['lp_show_views_and_comments'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_show_views_and_comments" id="lp_show_views_and_comments"', empty($modSettings['lp_show_views_and_comments']) ? '' : ' checked', ' value="1">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_frontpage_order_by_replies"></a> <span><label for="lp_frontpage_order_by_replies">', $txt['lp_frontpage_order_by_replies'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_frontpage_order_by_replies" id="lp_frontpage_order_by_replies"', empty($modSettings['lp_frontpage_order_by_replies']) ? '' : ' checked', ' value="1">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_frontpage_article_sorting_help" href="', $scripturl, '?action=helpadmin;help=lp_frontpage_article_sorting_help" onclick="return reqOverlayDiv(this.href);">
					<span class="main_icons help" title="', $txt['help'], '"></span>
				</a>
				<a id="setting_lp_frontpage_article_sorting"></a> <span><label for="lp_frontpage_article_sorting">', $txt['lp_frontpage_article_sorting'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<select name="lp_frontpage_article_sorting" id="lp_frontpage_article_sorting">';

	foreach ($txt['lp_frontpage_article_sorting_set'] as $value => $label) {
		echo '
					<option value="', $value, '"', ! empty($modSettings['lp_frontpage_article_sorting']) && $modSettings['lp_frontpage_article_sorting'] == $value ? ' selected' : '', '>', $label, '</option>';
	}

	echo '
				</select>
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_frontpage_layout"></a> <span><label for="lp_frontpage_layout">', $txt['lp_frontpage_layout'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<select name="lp_frontpage_layout" id="lp_frontpage_layout">';

	foreach ($context['lp_frontpage_layouts'] as $value => $label) {
		echo '
					<option value="', $value, '"', ! empty($modSettings['lp_frontpage_layout']) && $modSettings['lp_frontpage_layout'] === $value ? ' selected' : '', '>', $label, '</option>';
	}

	echo '
				</select>
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_frontpage_num_columns"></a> <span><label for="lp_frontpage_num_columns">', $txt['lp_frontpage_num_columns'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<select name="lp_frontpage_num_columns" id="lp_frontpage_num_columns">';

	foreach ($context['lp_column_set'] as $value => $label) {
		echo '
					<option value="', $value, '"', ! empty($modSettings['lp_frontpage_num_columns']) && $modSettings['lp_frontpage_num_columns'] == $value ? ' selected' : '', '>
						', $label, '
					</option>';
	}

	echo '
				</select>
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_show_pagination"></a> <span><label for="lp_show_pagination">', $txt['lp_show_pagination'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<select name="lp_show_pagination" id="lp_show_pagination">';

	foreach ($txt['lp_show_pagination_set'] as $value => $label) {
		echo '
					<option value="', $value, '"', ! empty($modSettings['lp_show_pagination']) && $modSettings['lp_show_pagination'] == $value ? ' selected' : '', '>', $label, '</option>';
	}

	echo '
				</select>
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_use_simple_pagination"></a> <span><label for="lp_use_simple_pagination">', $txt['lp_use_simple_pagination'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_use_simple_pagination" id="lp_use_simple_pagination"', empty($modSettings['lp_use_simple_pagination']) ? '' : ' checked', ' value="1">
			</dd>
		</template>

		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_num_items_per_page"></a> <span><label for="lp_num_items_per_page">', $txt['lp_num_items_per_page'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="number" name="lp_num_items_per_page" id="lp_num_items_per_page" value="', $modSettings['lp_num_items_per_page'] ?? 10, '" size="6" min="1">
			</dd>
		</template>';
}

function template_callback_standalone_mode_settings()
{
	global $modSettings, $txt, $scripturl, $boardurl;

	echo '
	</dl>
	<dl
		class="settings"
		style="margin-top: -2em"
		x-data="{ standalone_mode: ', empty($modSettings['lp_standalone_mode']) ? 'false' : 'true', ', frontpage_mode: \'', $modSettings['lp_frontpage_mode'] ?? 0, '\' }"
		@change-mode.window="frontpage_mode = $event.detail.front"
	>
		<dt>
			<a id="setting_lp_standalone_mode"></a> <span><label for="lp_standalone_mode">', $txt['lp_action_on'], '</label></span>
		</dt>
		<dd>
			<input
				type="checkbox"
				name="lp_standalone_mode"
				id="lp_standalone_mode"
				value="1"', empty($modSettings['lp_standalone_mode']) ? '' : ' checked', '
				@change="standalone_mode = ! standalone_mode"
				:disabled="[\'0\', \'chosen_page\'].includes(frontpage_mode)"
			>
		</dd>

		<template x-if="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a id="setting_lp_standalone_url_help" href="', $scripturl, '?action=helpadmin;help=lp_standalone_url_help" onclick="return reqOverlayDiv(this.href);">
					<span class="main_icons help" title="', $txt['help'], '"></span>
				</a>
				<a id="setting_lp_standalone_url"></a> <span><label for="lp_standalone_url">', $txt['lp_standalone_url'], '</label></span>
			</dt>
		</template>
		<template x-if="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input
					type="text"
					name="lp_standalone_url"
					id="lp_standalone_url"
					value="', $modSettings['lp_standalone_url'] ?? '', '"
					size="80"
					placeholder="', $txt['lp_example'], $boardurl, '/portal.php"
				>
			</dd>
		</template>

		<template x-if="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dt>
				<a
					id="setting_lp_disabled_actions_help"
					href="', $scripturl, '?action=helpadmin;help=lp_disabled_actions_help"
					onclick="return reqOverlayDiv(this.href);"
				>
					<span class="main_icons help" title="', $txt['help'], '"></span>
				</a>
				<a id="setting_lp_disabled_actions"></a> <span><label for="lp_disabled_actions">', $txt['lp_disabled_actions'], '</label><br><span class="smalltext">', $txt['lp_disabled_actions_subtext'], '</span></span>
			</dt>
		</template>
		<template x-if="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input
					type="text"
					name="lp_disabled_actions"
					id="lp_disabled_actions"
					value="', $modSettings['lp_disabled_actions'] ?? '', '"
					size="80"
					placeholder="', $txt['lp_example'], 'mlist,calendar"
				>
			</dd>
		</template>';
}

function template_callback_comment_settings()
{
	global $modSettings, $txt, $scripturl, $context;

	echo '
	</dl>
	<dl class="settings" style="margin-top: -1em" x-data="{ comment_block: \'', $modSettings['lp_show_comment_block'] ?? 'none', '\' }">
		<dt>
			<a id="setting_lp_show_comment_block"></a> <span><label for="lp_show_comment_block">', $txt['lp_show_comment_block'], '</label></span>
		</dt>
		<dd>
			<select name="lp_show_comment_block" id="lp_show_comment_block" @change="comment_block = $event.target.value">';

	foreach ($txt['lp_show_comment_block_set'] as $value => $label) {
		echo '
				<option value="', $value, '"', ! empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] === $value ? ' selected' : '', '>', $label, '</option>';
	}

	echo '
			</select>
		</dd>

		<template x-if="comment_block === \'default\'">
			<dt>
				<a id="setting_lp_disabled_bbc_in_comments"></a>
				<span>
					<label for="lp_disabled_bbc_in_comments">', $txt['lp_disabled_bbc_in_comments'], '</label>
				</span>
				<div class="smalltext">
					', sprintf($txt['lp_disabled_bbc_in_comments_subtext'], $scripturl . '?action=admin;area=featuresettings;sa=bbc;' . $context['session_var'] . '=' . $context['session_id'] . '#disabledBBC'), '
				</div>
			</dt>
		</template>
		<template x-if="comment_block === \'default\'">
			<dd>
				<fieldset>
					<select id="lp_disabled_bbc_in_comments" name="lp_disabled_bbc_in_comments_enabledTags" multiple>';

	foreach ($context['bbc_sections']['columns'] as $bbcColumn) {
		foreach ($bbcColumn as $bbcTag) {
			echo '
						<option id="tag_lp_disabled_bbc_in_comments_', $bbcTag, '" value="', $bbcTag, '"', in_array($bbcTag, $context['bbc_sections']['disabled']) ? '' : ' selected', '>
							', $bbcTag, '
						</option>';
		}
	}

	echo '
					</select>
					<input type="checkbox" id="lp_disabled_bbc_in_comments_select_all" @click="toggleSelectAll($event.target)"', $context['bbc_sections']['all_selected'] ? ' selected' : '', '> <label for="lp_disabled_bbc_in_comments_select_all"><em>', $txt['enabled_bbc_select_all'], '</em></label>
					<script>
						VirtualSelect.init({
							ele: "#lp_disabled_bbc_in_comments",', ($context['right_to_left'] ? '
							textDirection: "rtl",' : ''), '
							dropboxWrapper: "body",
							maxWidth: "100%",
							search: true,
							showValueAsTags: true,
							showSelectedOptionsFirst: true,
							placeholder: "', $txt['no'], '",
							noSearchResultsText: "', $txt['no_matches'], '",
							searchPlaceholderText: "', $txt['search'], '",
							clearButtonText: "', $txt['remove'], '"
						});
						function toggleSelectAll(target) {
							document.querySelector("#lp_disabled_bbc_in_comments").toggleSelectAll(target.checked);
						}
					</script>
				</fieldset>
			</dd>
		</template>

		<template x-if="comment_block === \'default\'">
			<dt>
				<a id="setting_lp_time_to_change_comments"></a> <span><label for="lp_time_to_change_comments">', $txt['lp_time_to_change_comments'], '</label></span>
			</dt>
		</template>
		<template x-if="comment_block === \'default\'">
			<dd>
				<input type="number" name="lp_time_to_change_comments" id="lp_time_to_change_comments" value="', $modSettings['lp_time_to_change_comments'] ?? 0, '" size="6" min="0"> ', $txt['manageposts_minutes'], '
			</dd>
		</template>

		<template x-if="comment_block === \'default\'">
			<dt>
				<a id="setting_lp_num_comments_per_page"></a> <span><label for="lp_num_comments_per_page">', $txt['lp_num_comments_per_page'], '</label></span>
			</dt>
		</template>
		<template x-if="comment_block === \'default\'">
			<dd>
				<input type="number" name="lp_num_comments_per_page" id="lp_num_comments_per_page" value="', $modSettings['lp_num_comments_per_pages'] ?? 10, '" size="6" min="1">
			</dd>
		</template>

		<template x-if="comment_block === \'default\'">
			<dt>
				<a id="setting_lp_comment_sorting"></a> <span><label for="lp_comment_sorting">', $txt['lp_comment_sorting'], '</label></span>
			</dt>
		</template>
		<template x-if="comment_block === \'default\'">
			<dd>
				<select id="lp_comment_sorting" name="lp_comment_sorting">';

	foreach ([$txt['lp_sort_by_created'], $txt['lp_sort_by_created_desc'], $txt['lp_sort_by_rating']] as $sort_value => $sort_title) {
		echo '
					<option value="', $sort_value, '"', ! empty($modSettings['lp_comment_sorting']) && $modSettings['lp_comment_sorting'] == $sort_value ? ' selected' : '', '>', $sort_title, '</option>';
	}

	echo '
				</select>
			</dd>
		</template>

		<template x-if="comment_block === \'default\'">
			<dt>
				<a id="setting_lp_allow_comment_ratings"></a> <span><label for="lp_allow_comment_ratings">', $txt['lp_allow_comment_ratings'], '</label></span>
			</dt>
		</template>
		<template x-if="comment_block === \'default\'">
			<dd>
				<input type="checkbox" name="lp_allow_comment_ratings" id="lp_allow_comment_ratings"', empty($modSettings['lp_allow_comment_ratings']) ? '' : ' checked', ' value="1">
			</dd>
		</template>';
}

function template_lp_category_settings()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_categories_manage'], '</h3>
	</div>
	<div class="windowbg noup">
		<dl class="lp_categories settings" x-data>
			<dt>
				<form accept-charset="', $context['character_set'], '">
					<table class="table_grid">
						<tbody id="lp_categories" x-ref="category_list">';

	foreach ($context['lp_categories'] as $id => $cat)
		show_single_category($id, $cat);

	echo '
						</tbody>
					</table>
				</form>
			</dt>
			<dd>
				<div class="roundframe">
					<div class="noticebox">
						<form
							id="add_category_form"
							name="add_category_form"
							accept-charset="', $context['character_set'], '"
							@submit.prevent="category.add($refs)"
						>
							<input
								name="new_category_name"
								type="text"
								placeholder="', $txt['title'], '"
								maxlength="255"
								form="add_category_form"
								required
								x-ref="cat_name"
							>
							<textarea
								placeholder="', $txt['lp_categories_desc'], '"
								maxlength="255"
								x-ref="cat_desc"
							></textarea>
						</form>
					</div>
					<div class="centertext">
						<input form="add_category_form" class="button" type="submit" value="', $txt['lp_categories_add'], '">
					</div>
				</div>
			</dd>
		</dl>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
	<script>
		const category = new Category();
		new Sortable(document.getElementById("lp_categories"), {
			handle: ".handle",
			animation: 150,
			onSort: e => category.updatePriority(e)
		});
	</script>';
}

function show_single_category(int $id, array $cat)
{
	global $txt, $context;

	echo '
	<tr class="windowbg" data-id="', $id, '" x-data>
		<td class="centertext handle">', $context['lp_icon_set']['arrows'], '</td>
		<td>
			<span class="floatright">
				<span @click="category.remove($root)" title="', $txt['remove'], '" class="error">&times;</span>
			</span>
			<label for="category_name', $id, '" class="handle">', $txt['lp_category'], ' #', $id, '</label>
			<input
				type="text"
				value="', $cat['name'], '"
				maxlength="255"
				@change="category.updateName($root, $event.target)"
			>
			<br>
			<textarea
				id="category_desc', $id, '"
				rows="2"
				placeholder="', $txt['lp_page_description'], '"
				maxlength="255"
				@change="category.updateDescription($root, $event.target.value)"
			>', $cat['desc'], '</textarea>
		</td>
	</tr>';
}

function template_callback_panel_layout()
{
	global $txt, $modSettings, $context;

	echo '
		</dl>
	</div>
	<div class="windowbg">', $txt['lp_panel_layout_preview'], '</div>
	<div class="generic_list_wrapper">
		<div class="centertext', empty($modSettings['lp_swap_header_footer']) ? '' : ' column-reverse', '">
			<div class="row center-xs">
				<div class="col-xs-', $context['lp_header_panel_width'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['header'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_header_panel_width">col-xs-</label>
						<select id="lp_header_panel_width" name="lp_header_panel_width">';

		foreach ($context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', $context['lp_header_panel_width'] == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
			<div class="row', empty($modSettings['lp_swap_left_right']) ? '' : ' reverse', '">
				<div class="col-xs-12 col-sm-12 col-md-', $context['lp_left_panel_width']['md'], ' col-lg-', $context['lp_left_panel_width']['lg'], ' col-xl-', $context['lp_left_panel_width']['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['left'], '</h3>
					</div>
					<div class="information">
						<ul class="righttext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[md]">col-md-</label>
								<select id="lp_left_panel_width[md]" name="lp_left_panel_width[md]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['md'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[lg]">col-lg-</label>
								<select id="lp_left_panel_width[lg]" name="lp_left_panel_width[lg]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[xl]">col-xl-</label>
								<select id="lp_left_panel_width[xl]" name="lp_left_panel_width[xl]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_left_panel_sticky">', $txt['lp_left_panel_sticky'], '</label>
						<input type="checkbox" id="lp_left_panel_sticky" name="lp_left_panel_sticky"', empty($modSettings['lp_left_panel_sticky']) ? '' : ' checked="checked"', '>
					</div>
				</div>
				<div class="col-xs">
					<div class="windowbg', empty($modSettings['lp_swap_top_bottom']) ? '' : ' column-reverse', '">
						<strong>col-xs (auto)</strong>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', $context['lp_block_placements']['top'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="descbox alternative">
									<strong>', $context['lp_icon_set']['content'], '</strong>
									<div>', $txt['lp_content'], '</div>
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', $context['lp_block_placements']['bottom'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-', $context['lp_right_panel_width']['md'], ' col-lg-', $context['lp_right_panel_width']['lg'], ' col-xl-', $context['lp_right_panel_width']['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['right'], '</h3>
					</div>
					<div class="information">
						<ul class="righttext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[md]">col-md-</label>
								<select id="lp_right_panel_width[md]" name="lp_right_panel_width[md]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['md'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[lg]">col-lg-</label>
								<select id="lp_right_panel_width[lg]" name="lp_right_panel_width[lg]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[xl]">col-xl-</label>
								<select id="lp_right_panel_width[xl]" name="lp_right_panel_width[xl]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_right_panel_sticky">', $txt['lp_right_panel_sticky'], '</label>
						<input type="checkbox" id="lp_right_panel_sticky" name="lp_right_panel_sticky"', empty($modSettings['lp_right_panel_sticky']) ? '' : ' checked="checked"', '>
					</div>
				</div>
			</div>
			<div class="row center-xs">
				<div class="col-xs-', $context['lp_footer_panel_width'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['footer'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_footer_panel_width">col-xs-</label>
						<select id="lp_footer_panel_width" name="lp_footer_panel_width">';

		foreach ($context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', $context['lp_footer_panel_width'] == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br>';
}

function template_callback_panel_direction()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_panel_direction'], '</h3>
	</div>
	<div class="information">', $txt['lp_panel_direction_note'], '</div>
	<div class="generic_list_wrapper">
		<table class="table_grid centertext">
			<tbody>';

	foreach ($context['lp_block_placements'] as $key => $label) {
		echo '
				<tr class="windowbg">
					<td>
						<label for="lp_panel_direction_' . $key . '">', $label, '</label>
					</td>
					<td>
						<select id="lp_panel_direction[' . $key . ']" name="lp_panel_direction[' . $key . ']">';

		foreach ($txt['lp_panel_direction_set'] as $value => $direction) {
			echo '
							<option value="', $value, '"', !empty($context['lp_panel_direction'][$key]) && $context['lp_panel_direction'][$key] == $value ? ' selected' : '', '>', $direction, '</option>';
		}

		echo '
						</select>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	<dl class="settings">';
}

function template_post_tab(array $fields, string $tab = 'content')
{
	global $context;

	$fields['subject'] = ['no'];

	foreach ($fields as $pfid => $pf) {
		if (empty($pf['input']['tab']))
			$pf['input']['tab'] = 'tuning';

		if ($pf['input']['tab'] != $tab)
			$fields[$pfid] = ['no'];
	}

	$context['posting_fields'] = $fields;

	LoadTemplate('Post');

	template_post_header();
}
