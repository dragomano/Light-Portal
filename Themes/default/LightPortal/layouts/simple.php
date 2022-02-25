<?php

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 */
function template_show_articles_simple()
{
	global $context, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_simple_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">';

		if (! empty($article['image'])) {
			echo '
			<div class="article_image lazy" data-bg="', $article['image'], '"></div>';
		}

		echo '
			<div class="mt-6 body">
				<a class="article_title" href="', $article['link'], '">', $article['title'], '</a>';

		if (! empty($article['teaser'])) {
			echo '
				<p class="article_teaser">', $article['teaser'], '</p>';
		}

		echo '
			</div>
			<div class="mt-6">
				<a class="bbc_link" href="', $article['link'], '">', $txt['lp_read_more'], '</a>
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
