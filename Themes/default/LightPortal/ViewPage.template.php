<?php

// The portal page template
// Шаблон просмотра страницы портала
function template_show_page()
{
	global $context, $scripturl, $txt, $settings, $modSettings, $boardurl;

	echo '
	<section itemscope itemtype="http://schema.org/Article">
		<div class="cat_bar">
			<h3 class="catbg" itemprop="headline">', $context['page_title'], $context['lp_page']['can_edit'] ? '<a class="floatright" href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '</h3>
		</div>
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', $context['canonical_url'], '" content="', $context['canonical_url'], '">
		<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
			<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
				<img itemprop="url image" src="', $context['header_logo_url_html_safe'] ?: ($settings['images_url'] . '/thumbnail.png'), '" style="display:none" alt="">
			</span>
			<meta itemprop="name" content="', $context['forum_name_html_safe'], '">
			<meta itemprop="address" content="', !empty($modSettings['lp_page_itemprop_address']) ? $modSettings['lp_page_itemprop_address'] : $boardurl, '">
			<meta itemprop="telephone" content="', !empty($modSettings['lp_page_itemprop_phone']) ? $modSettings['lp_page_itemprop_phone'] : '', '">
		</span>';

	if (!empty($context['lp_page']['options']['show_author_and_date'])) {
		echo '
		<div class="information">
			<span class="floatleft"><i class="fas fa-user" aria-hidden="true"></i> <span itemprop="author">', $context['lp_page']['author'], '</span></span>
			<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
				<i class="fas fa-clock" aria-hidden="true"></i> ', $context['lp_page']['created'], !empty($context['lp_page']['updated_at']) ? ' (<meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '">' . $txt['modified_time'] . ': ' . $context['lp_page']['updated'] . ')' : '', '
			</time>
		</div>';
	}

	echo '
		<article class="roundframe', !empty($context['lp_page']['options']['show_author_and_date']) ? '' : ' noup', '" itemprop="articleBody">';

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

		foreach ($context['lp_frontpage_articles'] as $topic) {
			echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], ' col-lg-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $topic['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a data-id="', $topic['id'], '" href="', $topic['link'], '">', $topic['subject'], '</a>', '
				</h3>
			</div>
			<div class="roundframe noup', $topic['css_class'], '">';

			if (!empty($topic['image']))
				echo '
				<img class="article_image" src="', $topic['image'], '" alt="', $topic['subject'], '">';

			echo '
				<div class="article_info">
					<div class="author_and_category">
						<div class="floatleft">';

			if (!empty($topic['poster_id']))
				echo '
							<a href="' . $topic['poster_link'] . '" title="' . $txt['profile_of'] . ' ' . $topic['poster_name'] . '">' . $topic['poster_name'] . '</a>';
			else
				echo $topic['poster_name'];

			echo '
						</div>
						<div class="floatright">
						', $topic['board'], '
						</div>
					</div>
					<div class="date_and_views smalltext">
						<div class="floatleft">', $topic['time'], '</div>
						<div class="floatright">
							<i class="fas fa-eye"></i> ', $topic['num_views'], '
						</div>
					</div>
				</div>';

			if (!empty($topic['preview']))
				echo '
				<div class="article_content title_bar">
					<p class="titlebg">', $topic['preview'], '</p>
				</div>';

			echo '
				<div class="article_link centertext">
					<a class="bbc_link" href="', $topic['link'], '">', $txt['lp_read_more'], '</a>
				</div>
			</div>
		</div>';
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
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

		foreach ($context['lp_frontpage_articles'] as $page) {
			if ($page['can_show']) {
				echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $page['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a href="', $page['link'], '">', $page['title'], '</a>', '
					', $page['can_edit'] ? '<a class="floatright" href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '
				</h3>
			</div>
			<div class="roundframe noup">';

			if (!empty($page['image']))
				echo '
				<img class="article_image" src="', $page['image'], '" alt="', $page['title'], '">';

			echo '
				<div class="article_info">
					<div class="author_and_category">
						<div class="floatleft">&nbsp;</div>
						<div class="floatright">';

			if (!empty($page['author_id']))
				echo '
							<a href="' . $page['author_link'] . '" title="' . $txt['profile_of'] . ' ' . $page['author_name'] . '">' . $page['author_name'] . '</a>';
			else
				echo $txt['guest'];

			echo '
						</div>
					</div>
					<div class="date_and_views smalltext">
						<div class="floatleft">', $page['created_at'], '</div>
						<div class="floatright">
							<i class="fas fa-eye"></i> ', $page['num_views'], '
						</div>
					</div>
				</div>';

			if (!empty($page['description']))
				echo '
				<div class="article_content title_bar page_', $page['type'], '">
					<p class="titlebg">', $page['description'], '</p>
				</div>';

			echo '
				<div class="article_link centertext">
					<a class="bbc_link" href="', $page['link'], '">', $txt['lp_read_more'], '</a>
				</div>
			</div>
		</div>';
			}
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';

		portal_frontpage_scripts();
	}
}

// Selected boards as sources of articles
// Выбранные разделы в виде статей
function template_show_boards_as_articles()
{
	global $context, $txt;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $board) {
			echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], ' col-lg-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $board['is_updated'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a data-id="', $board['id'], '" href="', $board['link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>', $board['name'], '</a>', '
				</h3>
			</div>
			<div class="roundframe noup">';

			if (!empty($board['image']))
				echo '
				<img class="article_image" src="', $board['image'], '" alt="', $board['name'], '">';

			echo '
				<div class="article_info">
					<div class="author_and_category">
						<div class="floatleft">&nbsp;</div>
						<div class="floatright">', $board['category'], '</div>
					</div>
					<div class="date_and_views smalltext">
						<div class="floatleft">';

			if (!empty($board['last_updated'])) {
				echo '
						', $board['last_updated'];
			} else
				echo '&nbsp;';

			echo '
						</div>
						<div class="floatright">';

			if ($board['is_redirect']) {
				echo '
						', $txt['redirect_board'];
			} else {
				echo '
						<i class="fas fa-comment"></i> ', $board['num_posts'];
			}

			echo '
						</div>
					</div>
				</div>';

			if (!empty($board['description']))
				echo '
				<div class="article_content title_bar">
					<p class="titlebg">', $board['description'], '</p>
				</div>';

			echo '
				<div class="article_link centertext">
					<a class="bbc_link" href="', $board['last_post'] ?? $board['link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>', $board['is_redirect'] ? $txt['go_caps'] : $txt['lp_read_more'], '</a>
				</div>
			</div>
		</div>';
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
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
			$(".lp_frontpage_articles .roundframe").matchHeight();';

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
