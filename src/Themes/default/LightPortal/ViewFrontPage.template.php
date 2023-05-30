<?php

function template_empty()
{
	global $txt;

	echo '
	<div class="infobox">', $txt['lp_no_items'], '</div>';
}

function template_wrong_template()
{
	global $txt;

	echo '
	<div class="errorbox">', $txt['lp_wrong_template'], '</div>';
}

function template_layout()
{
	global $context, $settings;

	echo $context['lp_layout'] ?? '';

	echo '
	<script>window.lazyLoadOptions = {};</script>
	<script type="module" src="', $settings['default_theme_url'], '/scripts/light_portal/lazyload.esm.min.js" async></script>';
}

/**
 * Шаблон списка сортировки для страниц рубрик и тегов
 *
 * Template of sort list for category pages and tags
 */
function template_sorting_above()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>';

	if (empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="information">', $txt['lp_no_items'], '</div>';
	} else {
		echo '
	<div class="information">';

		if (! empty($context['description'])) {
			echo '
		<div class="floatleft">', $context['description'], '</div>';
		}

		echo '
		<div class="floatright">
			<form method="post">
				<label for="sort">', $txt['lp_sorting_label'], '</label>
				<select id="sort" name="sort" onchange="this.form.submit()">
					<option value="title;desc"', $context['current_sorting'] == 'title;desc' ? ' selected' : '', '>', $txt['lp_sort_by_title_desc'], '</option>
					<option value="title"', $context['current_sorting'] == 'title' ? ' selected' : '', '>', $txt['lp_sort_by_title'], '</option>
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

function template_sorting_below()
{
}

function show_pagination(string $position = 'top')
{
	global $context, $modSettings;

	if (empty($context['lp_frontpage_articles']))
		return;

	$show_on_top = $position === 'top' && ! empty($modSettings['lp_show_pagination']);

	$show_on_bottom = $position === 'bottom' && (empty($modSettings['lp_show_pagination']) || ($modSettings['lp_show_pagination'] == 1));

	if (! empty($context['page_index']) && ($show_on_top || $show_on_bottom))
		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">', $context['page_index'], '</div>
		</div>';
}
