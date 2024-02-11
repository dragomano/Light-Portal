<?php

use Bugo\Compat\{Config, Lang, Theme, Utils};
use Bugo\LightPortal\Utils\Icon;

function template_empty(): void
{
	echo '
	<div class="infobox">', Lang::$txt['lp_no_items'], '</div>';
}

function template_wrong_template(): void
{
	echo '
	<div class="errorbox">', Lang::$txt['lp_wrong_template'], '</div>';
}

function template_layout(): void
{
	echo Utils::$context['lp_layout'] ?? '';

	echo '
	<script>window.lazyLoadOptions = {};</script>
	<script type="module" src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/lazyload.esm.min.js" async></script>';
}

/**
 * Список шаблонов оформления карточек для быстрого переключения
 *
 * List of template layouts for quick switching
 */
function template_layout_switcher_above(): void
{
	if (empty(Config::$modSettings['lp_show_layout_switcher']))
		return;

	if (empty(Utils::$context['lp_frontpage_articles']) || empty(Utils::$context['lp_frontpage_layouts']))
		return;

	echo '
	<div class="windowbg layout_switcher">
		<div class="floatleft">', Icon::get('views'), '</div>
		<div class="floatright">
			<form method="post">
				<label for="layout">', Lang::$txt['lp_template'], '</label>
				<select id="layout" name="layout" onchange="this.form.submit()">';

	foreach (Utils::$context['lp_frontpage_layouts'] as $layout => $title) {
		echo '
					<option value="', $layout, '"', Utils::$context['lp_current_layout'] === $layout ? ' selected' : '', '>
						', $title, '
					</option>';
	}

	echo '
				</select>
			</form>
		</div>
	</div>';
}

function template_layout_switcher_below()
{
}

/**
 * Шаблон списка сортировки для страниц рубрик и тегов
 *
 * Template of sort list for category pages and tags
 */
function template_sorting_above(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['page_title'], '</h3>
	</div>';

	if (empty(Utils::$context['lp_frontpage_articles'])) {
		echo '
	<div class="information">', Lang::$txt['lp_no_items'], '</div>';
	} else {
		echo '
	<div class="information">';

		if (! empty(Utils::$context['description'])) {
			echo '
		<div class="floatleft">', Utils::$context['description'], '</div>';
		}

		echo '
		<div class="floatright">
			<form method="post">
				<label for="sort">', Lang::$txt['lp_sorting_label'], '</label>
				<select id="sort" name="sort" onchange="this.form.submit()">
					<option value="title;desc"', Utils::$context['current_sorting'] == 'title;desc' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_title_desc'], '</option>
					<option value="title"', Utils::$context['current_sorting'] == 'title' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_title'], '</option>
					<option value="created;desc"', Utils::$context['current_sorting'] == 'created;desc' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_created_desc'], '</option>
					<option value="created"', Utils::$context['current_sorting'] == 'created' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_created'], '</option>
					<option value="updated;desc"', Utils::$context['current_sorting'] == 'updated;desc' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_updated_desc'], '</option>
					<option value="updated"', Utils::$context['current_sorting'] == 'updated' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_updated'], '</option>
					<option value="author_name;desc"', Utils::$context['current_sorting'] == 'author_name;desc' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_author_desc'], '</option>
					<option value="author_name"', Utils::$context['current_sorting'] == 'author_name' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_author'], '</option>
					<option value="num_views;desc"', Utils::$context['current_sorting'] == 'num_views;desc' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_num_views_desc'], '</option>
					<option value="num_views"', Utils::$context['current_sorting'] == 'num_views' ? ' selected' : '', '>', Lang::$txt['lp_sort_by_num_views'], '</option>
				</select>
			</form>
		</div>
	</div>';
	}
}

function template_sorting_below()
{
}

function show_pagination(string $position = 'top'): void
{
	if (empty(Utils::$context['lp_frontpage_articles']))
		return;

	$show_on_top = $position === 'top' && ! empty(Config::$modSettings['lp_show_pagination']);

	$show_on_bottom = $position === 'bottom' && (empty(Config::$modSettings['lp_show_pagination']) || (Config::$modSettings['lp_show_pagination'] == 1));

	if (! empty(Utils::$context['page_index']) && ($show_on_top || $show_on_bottom))
		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">', Utils::$context['page_index'], '</div>
		</div>';
}
