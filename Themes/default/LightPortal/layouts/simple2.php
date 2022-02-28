<?php

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 */
function template_show_articles_simple2()
{
	global $context, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_simple2_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12">
			<div class="card">
				<div class="card-header">
					<div class="card-image lazy" data-bg="', $article['image'], '"></div>
					<div class="card-title">
						<h3>', $article['title'], '</h3>';

		if (! empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '">', $article['date'], '</time>';
		}

		echo '
					</div>
					<svg viewBox="0 0 100 100" preserveAspectRatio="none">
						<polygon points="50,0 100,0 50,100 0,100" />
					</svg>
				</div>

				<div class="card-body">
					<div class="card-body-inner">';

		if (! empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '">', $article['date'], '</time>';
		}

		echo '
						<h3>', $article['title'], '</h3>';

		if (! empty($article['teaser'])) {
			echo '
						<p class="article_teaser">', $article['teaser'], '</p>';
		}

		echo '
						<a class="read_more" href="', $article['link'], '">
							<span>', $txt['lp_read_more'], '</span>
							<span class="arrow">&#x279c;</span>
						</a>
					</div>
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
