<?php

/**
 * Displaying articles in Flarum style
 *
 * Отображение статей в стиле Flarum
 */
function template_show_articles_flarum_style()
{
	echo '
	<style>
		.flarum_style .title_bar {
			margin-bottom: 2px;
		}

		.flarum_style .header_img {
			display: grid;
			place-content: center;
		}

		.flarum_style .header_area {
			display: block;
			margin-left: 20px;
			width: 70%;
			text-decoration: none;
		}

		.flarum_style .header_area h3 {
			margin: 0 0 3px;
			line-height: 1.3;
			font-weight: 400;
			font-size: 16px;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.flarum_style .header_area .smalltext {
			opacity: .5;
		}

		.flarum_style .header_area p {
			margin-bottom: 5px;
			overflow: hidden;
			display: -webkit-box;
			-webkit-line-clamp: 3;
			-webkit-box-orient: vertical;
			line-height: 1.4em;
		}
	</style>
	<div class="row">
		<div class="flarum_style col-xs-12">';

	show_pagination();

	show_articles();

	show_pagination('bottom');

	echo '
		</div>
	</div>';
}

function show_articles()
{
	global $context, $txt, $modSettings;

	if (empty($context['lp_frontpage_articles']))
		return;

	$context['lp_need_lower_case'] = in_array($txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']);

	$labels = ['lp_type_block', 'lp_type_editor', 'lp_type_comment', 'lp_type_parser', 'lp_type_article', 'lp_type_frontpage', 'lp_type_impex', 'lp_type_block_options', 'lp_type_page_options', 'lp_type_icons', 'lp_type_seo', 'lp_type_other', 'lp_type_ssi'];

	echo '
		<div class="title_bar">
			<h2 class="titlebg">', $context['page_title'], '</h2>
		</div>';

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="windowbg row">
			<div class="col-xs-12">
				<div class="row">
					<div class="header_img col-xs-2">
						<span>', empty($article['image']) ? $context['lp_icon_set']['big_image'] : ('<img class="avatar" loading="lazy" src="' . $article['image'] . '" alt="' . $article['title'] . '">'), '</span>
					</div>
					<div class="header_area col-xs">
						<h3>
							<a href="', $article['msg_link'], '">', $article['title'], '</a>', $article['is_new'] ? '
							<span class="new_posts">' . $txt['new'] . '</span> ' : '', '
						</h3>';

		if (! empty($article['section']['name'])) {
			echo '
						<div class="smalltext hidden-md hidden-lg hidden-xl">
							<span class="new_posts ', $labels[rand(0, count($labels) - 1)], '" href="', $article['section']['link'], '">', $article['section']['name'], '</span>';

			if (! empty($article['replies']['num']))
				echo '
							<span style="margin-left: 1em">' . str_replace(' class=', ' title="' . $article['replies']['title'] . '" class=', '<i class="far fa-comment"></i> '), $article['replies']['num'], '</span>';

			echo '
						</div>';
		}

		echo '
						<div class="smalltext">
							<span>', empty($article['replies']['num']) ? '' : $context['lp_icon_set']['reply'], '</span>';

		if (! empty($modSettings['lp_show_author']) && ! empty($article['author'])) {
			echo '
							<span>', $article['author']['name'] ?? $txt['guest_title'], '</span>';
		}

		echo '
							<span', $context['lp_need_lower_case'] ? ' style="text-transform: lowercase"' : '', '>', $article['date'], '</span>
						</div>
					</div>
					<div class="righttext smalltext hidden-xs hidden-sm col-xs-2">';

		if (! empty($article['section']['name'])) {
			echo '
						<a class="new_posts ', $labels[rand(0, count($labels) - 1)], '" href="', $article['section']['link'], '">', $article['section']['name'], '</a>';
		}

		if (! empty($article['replies']['num']))
			echo '
						<div>' . str_replace(' class=', ' title="' . $article['replies']['title'] . '" class=', '<i class="far fa-comment"></i> '), $article['replies']['num'], '</div>';

		echo '
					</div>
				</div>
			</div>';

		if (! empty($article['teaser'])) {
			echo '
			<div class="col-xs-12">', $article['teaser'], '</div>';
		}

		echo '
		</div>';
	}
}
