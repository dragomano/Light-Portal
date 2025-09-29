<?php declare(strict_types=1);

use Bugo\Compat\{Config, Lang};
use Bugo\Compat\{Theme, Utils};
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
	echo Utils::$context['lp_layout_content'] ?? '';

	echo '
	<script>window.lazyLoadOptions = {};</script>
	<script src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/lazyload.min.js" async></script>';
}

/**
 * List of template layouts for quick switching
 */
function template_toolbar_above(): void
{
	if (empty(Config::$modSettings['lp_show_layout_switcher']) && empty(Config::$modSettings['lp_show_sort_dropdown']))
		return;

	if (empty(Utils::$context['lp_frontpage_articles']) || empty(Utils::$context['lp_frontpage_layouts']))
		return;

	echo '
	<div class="windowbg frontpage_toolbar">
		<div class="floatleft">', Icon::get('views'), '</div>
		<div class="floatright">
			<form method="post">
				', show_layout_switcher(), '
				', show_sort_dropdown(), '
			</form>
		</div>
	</div>';
}

function template_toolbar_below()
{
}

function show_layout_switcher(): string
{
	if (empty(Config::$modSettings['lp_show_layout_switcher']))
		return '';

	$html = '
		<label for="layout"><i class="fa-solid fa-object-group"></i> <span class="sr-only">' . Lang::$txt['lp_template'] . '</span></label>
		<select id="layout" name="layout" onchange="this.form.submit()">';

	foreach (Utils::$context['lp_frontpage_layouts'] as $layout => $title) {
		$selected = Utils::$context['lp_current_layout'] === $layout ? ' selected' : '';
		$html .= '
			<option value="' . $layout . '"' . $selected . '>' . $title . '</option>';
	}

	return $html . '
		</select>';
}

function show_sort_dropdown(): string
{
	if (empty(Config::$modSettings['lp_show_sort_dropdown']) || empty(Utils::$context['lp_sorting_options']))
		return '';

	$html = '
		<label for="sort"><i class="fa-solid fa-arrow-down-z-a" aria-hidden="true"></i> <span class="sr-only">' . Lang::$txt['lp_sorting_label'] . '</span></label>
		<select id="sort" name="sort" onchange="this.form.submit()">';

	foreach (Utils::$context['lp_sorting_options'] as $value => $label) {
		$selected = Utils::$context['lp_current_sorting'] === $value ? ' selected' : '';
		$html .= '
			<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
	}

	return $html . '
		</select>';
}

/**
 * Template of header for category pages and tags
 */
function template_category_above(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', Utils::$context['page_title'];

	if (Utils::$context['user']['is_admin'] && isset(Utils::$context['lp_category_edit_link'])) {
		echo '
			<a class="floatright" href="', Utils::$context['lp_category_edit_link'], '">
				', Icon::get('edit'), '
				<span class="hidden-xs">', Lang::$txt['edit'], '</span>
			</a>';
	}

	echo '
		</h3>
	</div>';

	if (empty(Utils::$context['lp_frontpage_articles'])) {
		echo '
	<div class="information">', Lang::$txt['lp_no_items'], '</div>';
	}

	if (empty(Utils::$context['description']))
		return;

	echo '
	<div class="information">
		<div class="floatleft">', Utils::$context['description'], '</div>
	</div>';
}

function template_category_below()
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
