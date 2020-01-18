<?php

// The portal page template
// Шаблон просмотра страницы портала
function template_show_page()
{
	global $context, $scripturl, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], $context['lp_page']['can_edit'] ? '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="floatright fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '</h3>
	</div>
	<div class="roundframe page_', $context['lp_page']['type'], '">
		', $context['lp_page']['content'], '
	</div>';
}

// Topics from selected boards as sources of articles
// Темы из выбранных разделов в виде статей
function template_show_topics_as_articles()
{
	global $context, $txt;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		foreach ($context['lp_frontpage_articles'] as $topic) {
			echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $topic['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a data-id="', $topic['id'], '" href="', $topic['link'], '">', $topic['subject'], '</a>', '
				</h3>
			</div>
			<div class="roundframe noup', $topic['css_class'], '">
				<div>';

			if (!empty($topic['poster_id']))
				echo '
					<a href="' . $topic['poster_link'] . '" title="' . $txt['profile_of'] . ' ' . $topic['poster_name'] . '">' . $topic['poster_name'] . '</a>';
			else
				echo $topic['poster_name'];

			echo '
					<span class="floatright">', $topic['board'], '</span>
				</div>
				<div class="smalltext">
					', $topic['time'], '<span class="floatright">', $txt['views'], ': ', $topic['num_views'], '</span>
				</div>
				<a class="article_link" href="', $topic['link'], '">
					<div class="windowbg article_content">';

			if (!empty($topic['image']))
				echo '
						<img class="article_image" src="', $topic['image'], '" alt="', $topic['subject'], '">';

			echo '
						', $topic['preview'], '
					</div>
				</a>
			</div>
		</div>';
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		echo '
	</div>';

		portal_frontpage_scripts();
	}
}

// Pages as sources of articles
// Страницы в виде статей
function template_show_pages_as_articles()
{
	global $context, $txt, $scripturl;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		foreach ($context['lp_frontpage_articles'] as $page) {
			if ($page['can_show']) {
				echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $page['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a href="', $page['link'], '">', $page['title'], '</a>', '
					', $page['can_edit'] ? '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="floatright fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '
				</h3>
			</div>
			<div class="roundframe noup">
				<div>';

				if (!empty($page['author_id']))
					echo '
					<a href="' . $page['author_link'] . '" title="' . $txt['profile_of'] . ' ' . $page['author_name'] . '">' . $page['author_name'] . '</a>';
				else
					echo $page['author_name'];

				echo '
				</div>
				<div class="smalltext">', $page['created_at'], '<span class="floatright">', $txt['views'], ': ', $page['num_views'], '</span></div>
				<a class="article_link" href="', $page['link'], '">
					<div class="windowbg article_content page_', $page['type'], '">';

				if (!empty($topic['image']))
					echo '
						<img class="article_image" src="', $page['image'], '" alt="', $page['title'], '">';

				echo '
						', $page['description'], '
					</div>
				</a>
			</div>
		</div>';
			}
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		echo '
	</div>';

		portal_frontpage_scripts();
	}
}

// Possibility to load various scripts
// Возможность загружать различные скрипты
function portal_frontpage_scripts()
{
	global $settings, $context;

	echo '
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/jquery.matchHeight-min.js"></script>
	<script>
		jQuery(document).ready(function($) {
			$(".lp_frontpage_articles .article_content").matchHeight();';

	foreach ($context['lp_frontpage_articles'] as $topic) {
		if (!empty($topic['rating'])) {
			$img = '';
			for ($i = 0; $i < $topic['rating']; $i++)
				$img .= '<span class="topic_stars">&nbsp;&nbsp;&nbsp;</span>';

			echo '
			let starImg', $topic['id'], ' = $(".catbg a[data-id=', $topic['id'], ']");
			starImg', $topic['id'], '.after(\'<span class="topic_stars_main">', $img, '</span>\');';
		}
	}

	echo '
		});
	</script>';
}
