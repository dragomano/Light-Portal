<?php

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 */
function template_show_articles_alt()
{
	global $context, $txt, $modSettings;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_alt_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe">
				<header>
					<div class="title_bar">
						<h3>
							<a href="', $article['msg_link'], '">', $article['title'], '</a>', $article['is_new'] ? (' <span class="new_posts">' . $txt['new'] . '</span>') : '', '
						</h3>
					</div>
					<div>';

		if (! empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatleft">';

			if (! empty($article['views']['num']))
				echo str_replace(' class=', ' title="' . $article['views']['title'] . '" class=', $context['lp_icon_set']['views']), $article['views']['num'];

			if (! empty($article['views']['after']))
				echo $article['views']['after'];

			if (! empty($article['replies']['num']))
				echo ' ' . str_replace(' class=', ' title="' . $article['replies']['title'] . '" class=', $context['lp_icon_set']['replies']), $article['replies']['num'];

			if (! empty($article['replies']['after']))
				echo $article['replies']['after'];

			echo '
						</span>';
		}

		if (! empty($article['section']['name'])) {
			echo '
						<a class="floatright" href="', $article['section']['link'], '">', $context['lp_icon_set']['category'], $article['section']['name'], '</a>';
		}

		echo '
					</div>';

		if (! empty($article['image'])) {
			echo '
					<img class="lazy" data-src="', $article['image'], '" width="443" height="221" alt="', $article['title'], '">';
		}

		echo '
				</header>
				<div class="article_body">';

		if (! empty($article['teaser'])) {
			echo '
					<p>', $article['teaser'], '</p>';
		}

		echo '
				</div>
				<div class="article_footer">
					<div class="centertext">
						<a class="bbc_link" href="', $article['link'], '">', $txt['lp_read_more'], '</a>
					</div>
					<div class="centertext">';

		if (! empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '">', $context['lp_icon_set']['date'], $article['date'], '</time>';
		}

		if (! empty($modSettings['lp_show_author']) && ! empty($article['author'])) {
			if (! empty($article['author']['id']) && ! empty($article['author']['name'])) {
				echo '
						| ', $context['lp_icon_set']['user'], '<a href="', $article['author']['link'], '" class="card_author">', $article['author']['name'], '</a>';
			} else {
				echo '
						| <span class="card_author">', $txt['guest_title'], '</span>';
			}
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
