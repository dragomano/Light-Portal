<?php

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 */
function template_show_articles_alt3()
{
	global $context, $modSettings, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_alt3_view">';

	show_pagination();

	$i = 0;
	foreach ($context['lp_frontpage_articles'] as $article) {
		$i++;

		echo '
		<div class="card', $i % 2 === 0 ? ' alt': '', ' col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">
			<div class="meta">';

		if (! empty($article['image'])) {
			echo '
				<div class="photo lazy" data-bg="', $article['image'], '"></div>';
		}

		echo '
				<ul class="details">';

		if (! empty($modSettings['lp_show_author']) && ! empty($article['author'])) {
			echo '
					<li class="author">
						', $context['lp_icon_set']['user'];

			if (! empty($article['author']['id']) && ! empty($article['author']['name'])) {
				echo '
						<a href="', $article['author']['link'], '">', $article['author']['name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}

			echo '
					</li>';
		}

		if (! empty($article['datetime'])) {
			echo '
					<li class="date">', $context['lp_icon_set']['calendar'], '<time datetime="', $article['datetime'], '">', $article['date'], '</time></li>';
		}

		if (! empty($article['tags'])) {
			echo '
					<li class="tags">
						', $context['lp_icon_set']['tag'], '
						<ul style="display: inline">';

			foreach ($article['tags'] as $key) {
				echo '
							<li><a href="', $key['href'], '">', $key['name'], '</a></li>';
			}

			echo '
						</ul>
					</li>';
		}

		echo '
				</ul>
			</div>
			<div class="description">
				<h1><a href="', $article['link'], '">', $article['title'], '</a></h1>';

		if (! empty($article['section']['name'])) {
			echo '
				<h2><a href="', $article['section']['link'], '">', $context['lp_icon_set']['category'], $article['section']['name'], '</a></h2>';
		}

		if (! empty($article['teaser'])) {
			echo '
				<p>', $article['teaser'], '</p>';
		}

		echo '
				<div class="read_more">
					<a class="bbc_link" href="', $article['msg_link'], '">', $txt['lp_read_more'], '</a> ', $context['lp_icon_set']['arrow_right'], '
				</div>
			</div>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}
