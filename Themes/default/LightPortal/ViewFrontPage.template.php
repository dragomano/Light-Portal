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

/**
 * Default template view for frontpage articles
 *
 * Дефолтный шаблон отображения статей на главной
 */
function template_show_articles()
{
	global $context, $modSettings, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe', $article['css_class'] ?? '', '">';

		if (! empty($article['image'])) {
			if ($article['is_new']) {
				echo '
					<div class="new_hover">
						<div class="new_icon">
							<span class="new_posts">', $txt['new'], '</span>
						</div>
					</div>';
			}

			if ($article['can_edit']) {
				echo '
					<div class="info_hover">
						<div class="edit_icon">
							<a href="', $article['edit_link'], '">
								', str_replace(' class=', ' title="' . $txt['edit'] . '" class=', $context['lp_icon_set']['edit']), '
							</a>
						</div>
					</div>';
			}

			echo '
				<div class="card_img"></div>
				<a href="', $article['link'], '">
					<div class="card_img_hover lazy" data-bg="', $article['image'], '"></div>
				</a>';
		}

		echo '
				<div class="card_info">
					<span class="card_date smalltext">';

		if (! empty($article['section']['name'])) {
			echo '
						<a class="floatleft" href="', $article['section']['link'], '">', $context['lp_icon_set']['category'], $article['section']['name'], '</a>';
		}

		if ($article['is_new'] && empty($article['image'])) {
			echo '
						&nbsp;<span class="new_posts">', $txt['new'], '</span>';
		}


		if (! empty($article['datetime'])) {
			echo '
						<time class="floatright" datetime="', $article['datetime'], '">', $context['lp_icon_set']['date'], $article['date'], '</time>';
		}

		echo '
					</span>
					<h3>
						<a href="', $article['msg_link'], '">', $article['title'], '</a>
					</h3>';

		if (! empty($article['teaser'])) {
			echo '
					<p>', $article['teaser'], '</p>';
		}

		echo '
					<div>';

		if (! empty($article['category'])) {
			echo '
						<span class="card_author">', $context['lp_icon_set']['category'], $article['category'], '</span>';
		}

		if (! empty($modSettings['lp_show_author']) && ! empty($article['author'])) {
			if (! empty($article['author']['id']) && ! empty($article['author']['name'])) {
				echo '
						<a href="', $article['author']['link'], '" class="card_author">', $context['lp_icon_set']['user'], $article['author']['name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}
		}

		if (! empty($modSettings['lp_show_views_and_comments'])) {
			echo '
						<span class="floatright">';

			if (! empty($article['views']['num']))
				echo str_replace(' class=', ' title="' . $article['views']['title'] . '" class=', $context['lp_icon_set']['views']), $article['views']['num'];

			if (! empty($article['views']['after']))
				echo $article['views']['after'];

			if (! empty($article['is_redirect'])) {
				echo $context['lp_icon_set']['redirect'];
			} elseif (! empty($article['replies']['num'])) {
				echo ' ' . str_replace(' class=', ' title="' . $article['replies']['title'] . '" class=', $context['lp_icon_set']['replies']), $article['replies']['num'];
			}

			if (! empty($article['replies']['after']))
				echo $article['replies']['after'];

			echo '
						</span>';
		}

		echo '
					</div>
				</div>
			</article>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
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

	$show_on_top = $position === 'top' && ! empty($modSettings['lp_show_pagination']);

	$show_on_bottom = $position === 'bottom' && (empty($modSettings['lp_show_pagination']) || ($modSettings['lp_show_pagination'] == 1));

	if (! empty($context['page_index']) && ($show_on_top || $show_on_bottom))
		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">', $context['page_index'], '</div>
		</div>';
}
