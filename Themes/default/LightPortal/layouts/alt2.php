<?php

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 */
function template_show_articles_alt2()
{
	global $context, $modSettings, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_alt2_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<article class="descbox">';

		if (! empty($article['image'])) {
			echo '
			<a class="article_image_link" href="', $article['link'], '">
				<div class="lazy" data-bg="', $article['image'], '"></div>
			</a>';
		}

		echo '
			<div class="article_body">
				<div>
					<header>';

		if (! empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '">', $context['lp_icon_set']['date'], $article['date'], '</time>';
		}

		echo '
						<h3><a href="', $article['msg_link'], '">', $article['title'], '</a></h3>
					</header>';

		if (! empty($article['teaser'])) {
			echo '
					<section>
						<p>', $article['teaser'], '</p>
					</section>';
		}

		echo '
				</div>';

		if (! empty($modSettings['lp_show_author']) && ! empty($article['author'])) {
			echo '
				<footer>';

			if (! empty($article['author']['avatar']))
				echo $article['author']['avatar'];

			echo '
					<span>';

			if (! empty($article['author']['id']) && ! empty($article['author']['name'])) {
				echo '
						<a href="', $article['author']['link'], '">', $article['author']['name'], '</a>';
			} else {
				echo '
						<span>', $txt['guest_title'], '</span>';
			}

			echo '
					</span>
				</footer>';
		}

		echo '
			</div>
		</article>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}
