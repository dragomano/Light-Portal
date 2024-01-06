<?php

function template_callback_frontpage_mode_settings(): void
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
					placeholder="', str_replace(["'", "\""], "", $context['forum_name']), ' - ', $txt['lp_portal'], '"
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
				<a id="setting_lp_show_layout_switcher"></a> <span><label for="lp_show_layout_switcher">', $txt['lp_show_layout_switcher'], '</label></span>
			</dt>
		</template>
		<template x-if="! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
			<dd>
				<input type="checkbox" name="lp_show_layout_switcher" id="lp_show_layout_switcher"', empty($modSettings['lp_show_layout_switcher']) ? '' : ' checked', ' value="1">
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

function template_callback_standalone_mode_settings_before(): void
{
	global $modSettings;

	echo '
	<div
		x-data="{
			standalone_mode: ', empty($modSettings['lp_standalone_mode']) ? 'false' : 'true', ',
			frontpage_mode: \'', $modSettings['lp_frontpage_mode'] ?? 0, '\'
		}"
		@change-mode.window="frontpage_mode = $event.detail.front"
	>';
}

function template_callback_standalone_mode_settings_after(): void
{
	global $scripturl, $txt, $context;

	echo '
		<table class="lp_table_settings">
			<tbody>
				<tr>
					<template x-if="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
						<td>
							<a
								id="setting_lp_disabled_actions_help"
								href="', $scripturl, '?action=helpadmin;help=lp_disabled_actions_help"
								onclick="return reqOverlayDiv(this.href);"
							>
								<span class="main_icons help" title="', $txt['help'], '"></span>
							</a>
							<a id="setting_lp_disabled_actions"></a>
							<span>
								<label for="lp_disabled_actions">', $txt['lp_disabled_actions'], '</label>
								<br>
								<span class="smalltext">', $txt['lp_disabled_actions_subtext'], '</span>
							</span>
						</td>
					</template>
					<template x-if="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)">
						<td style="width: 44%">', $context['lp_disabled_actions_select'], '</td>
					</template>
				</tr>
			</tbody>
		</table>
	</div>';
}

function template_callback_comment_settings_before(): void
{
	global $modSettings;

	echo '
	<div x-data="{ comment_block: \'', $modSettings['lp_show_comment_block'] ?? 'none', '\' }">';
}

function template_callback_comment_settings_after(): void
{
	echo '
	</div>';
}

function template_post_tab(array $fields, string $tab = 'content'): bool
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

	return false;
}
