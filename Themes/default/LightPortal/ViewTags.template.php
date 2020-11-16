<?php

function template_show_tags_above()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>';

	if (empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="information">', $txt['lp_no_selected_tag'], '</div>';
	} else {
		echo '
	<div class="information">
		<div class="floatright">
			<form action="', $context['canonical_url'], '" method="post">
				<label for="sort">', $txt['lp_sorting_label'], '</label>
				<select id="sort" name="sort" onchange="this.form.submit()">
					<option value="created;desc"', $context['current_sorting'] == 'created;desc' ? ' selected' : '', '>', $txt['lp_sort_by_created_desc'], '</option>
					<option value="created"', $context['current_sorting'] == 'created' ? ' selected' : '', '>', $txt['lp_sort_by_created'], '</option>
					<option value="updated;desc"', $context['current_sorting'] == 'updated;desc' ? ' selected' : '', '>', $txt['lp_sort_by_updated_desc'], '</option>
					<option value="updated"', $context['current_sorting'] == 'updated' ? ' selected' : '', '>', $txt['lp_sort_by_updated'], '</option>
					<option value="author_name;desc"', $context['current_sorting'] == 'author_name;desc' ? ' selected' : '', '>', $txt['lp_sort_by_author_desc'], '</option>
					<option value="author_name"', $context['current_sorting'] == 'author_name' ? ' selected' : '', '>', $txt['lp_sort_by_author'], '</option>
					<option value="num_views;desc"', $context['current_sorting'] == 'num_views;desc' ? ' selected' : '', '>', $txt['lp_sort_by_num_views_desc'], '</option>
					<option value="num_views"', $context['current_sorting'] == 'num_views' ? ' selected' : '', '>', $txt['lp_sort_by_num_views'], '</option>
				</select>
			</form>
		</div>
	</div>';
	}
}

function template_show_tags_below()
{
}
