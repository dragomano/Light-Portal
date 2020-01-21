<?php

// The portal page template
// Шаблон просмотра страницы портала
function template_show_page()
{
	global $context, $scripturl, $txt, $settings, $modSettings, $boardurl;

	echo '
	<section itemscope itemtype="http://schema.org/Article">
		<div class="cat_bar">
			<h3 class="catbg" itemprop="headline">', $context['page_title'], $context['lp_page']['can_edit'] ? '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="floatright fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '</h3>
		</div>
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', $context['canonical_url'], '">
		<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
			<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
				<img itemprop="url image" src="', $context['header_logo_url_html_safe'] ?: ($settings['images_url'] . '/thumbnail.png'), '" style="display:none;">
			</span>
			<meta itemprop="name" content="', $context['forum_name_html_safe'], '">
			<meta itemprop="address" content="', !empty($modSettings['lp_page_itemprop_address']) ? $modSettings['lp_page_itemprop_address'] : $boardurl, '">
			<meta itemprop="telephone" content="', !empty($modSettings['lp_page_itemprop_phone']) ? $modSettings['lp_page_itemprop_phone'] : '', '">
		</span>
		<div class="information">
			<span class="floatleft"><i class="fas fa-user" aria-hidden="true"></i> <span itemprop="author">', $context['lp_page']['author'], '</span></span>
			<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
				<i class="fas fa-clock" aria-hidden="true"></i> ', $context['lp_page']['created'], !empty($context['lp_page']['updated_at']) ? ' (<meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '">' . $txt['modified_time'] . ': ' . $context['lp_page']['updated'] . ')' : '', '
			</time>
		</div>
		<article class="roundframe" itemprop="articleBody">';

	if (!empty($settings['og_image']))
		echo '
			<meta itemprop="image" content="', $settings['og_image'], '">';

	echo '
			<div class="page_', $context['lp_page']['type'], '">', $context['lp_page']['content'], '</div>
		</article>
	</section>';
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
					echo $txt['guest'];

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

// The portal credits template
// Шаблон просмотра копирайтов используемых компонентов портала
function template_portal_credits()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_used_components'], '</h3>
	</div>
	<div class="roundframe noup">
		<ul>';

	foreach ($context['lp_components'] as $item) {
		echo '
			<li class="windowbg">
				<a href="' . $item['link'] . '" target="_blank" rel="noopener">' . $item['title'] . '</a> ' . (isset($item['author']) ? ' | &copy; ' . $item['author'] : '') . ' | Licensed under <a href="' . $item['license']['link'] . '" target="_blank" rel="noopener">' . $item['license']['name'] . '</a>
			</li>';
	}

	echo '
		</ul>
	</div>';
}
