<?php

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 */
function template_show_articles_simple3()
{
	global $context;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	show_pagination();

	echo '
		<div class="lp_frontpage_articles article_simple3_view">';

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
			<div>';

		if (! empty($article['image'])) {
			echo '
				<img class="lazy" data-src="', $article['image'], '" width="311" height="155" alt="', $article['title'], '">';
		}

		echo '
				<div class="title">
					<div><a class="bbc_link" href="', $article['link'], '">', $article['title'], '</a></div>';

		if (! empty($article['teaser'])) {
			echo '
					<p>', $article['teaser'], '</p>';
		}

		echo '
				</div>';

		if (! empty($article['tags'])) {
			echo '
				<div class="tags">';

			foreach ($article['tags'] as $key) {
				echo '
					<a href="', $key['href'], '">#', $key['name'], '</a>';
			}

			echo '
				</div>';
		}

		echo '
			</div>';
	}

	echo '
		</div>';

	show_pagination('bottom');

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}
