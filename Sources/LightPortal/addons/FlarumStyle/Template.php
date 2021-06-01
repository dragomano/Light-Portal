<?php

/**
 * Display the flarum-like sidebar
 *
 * Отображаем flarum-подобную боковую панель
 *
 * @return void
 */
function show_ffs_sidebar()
{
	global $scripturl, $txt, $context;

	echo '
	<nav>
		<ul>
			<li>
				<div class="roundframe">
					<ul>
						<li>
							<i class="far fa-comments"></i> <a href="', $scripturl, '?action=forum">', $context['is_portal'] ? $txt['lp_forum'] : $txt['lp_flarum_style_addon_all_boards'], '</a>
						</li>
						<li>
							<i class="fas fa-th-large"></i> <a href="', $scripturl, $context['is_portal'] ? '?action=portal;sa=tags' : '?action=keywords', '">', $txt['lp_flarum_style_addon_tags'], '</a>
						</li>
					</ul>
				</div>
			</li>';

	if (!empty($context['lp_all_categories'])) {
		foreach ($context['lp_all_categories'] as $category) {
			echo '
			<li>
				<div class="title_bar">
					<h4 class="titlebg">', $category['name'], '</h4>
				</div>
				<div class="roundframe">
					<ul>';

			foreach ($category['boards'] as $board) {
				echo '
						<li>';

				if ($board['child_level']) {
					echo '
							<ul>
								<li style="margin-left: 1em">
									<i class="fas fa-chevron-circle-right"></i> <a href="', $scripturl, $context['is_portal'] ? ('?action=portal;sa=categories;id=' . $board['id']) : ('?board=' . $board['id'] . '.0'), '">', $board['name'], '</a>
								</li>
							</ul>';
				} else {
					echo '
							<i class="far fa-circle"></i> <a href="', $scripturl, $context['is_portal'] ? ('?action=portal;sa=categories;id=' . $board['id']) : ('?board=' . $board['id'] . '.0'), '">', $board['name'], '</a>';
				}

				echo '
						</li>';
			}

			echo '
					</ul>
				</div>
			</li>';
		}

	}

	echo '
		</ul>
	</nav>';
}

/**
 * @return void
 */
function template_show_articles_as_flarum_style()
{
	global $context, $txt;

	echo '
	<div class="row">
		<div class="col-xs-12">
			<div class="title_bar" style="margin-bottom: 1px">
				<h2 class="titlebg">', $context['page_title'], '</h2>
			</div>';

	if (!empty($context['lp_frontpage_articles'])) {
		foreach ($context['lp_frontpage_articles'] as $article) {
			echo '
			<div class="windowbg', $article['css_class'] ?? '', '">';

			if (!empty($article['image'])) {
				echo '
				<div class="floatleft">
					<img class="avatar" src="', $article['image'], '" alt="', $article['title'], '" loading="lazy">
				</div>';
			} else {
				echo '
				<div class="floatleft" style="width: 64px">
					<i class="far fa-image fa-5x"></i>
				</div>';
			}

			echo '
				<div class="floatleft" style="margin-left: 20px; width: 70%">
					<h3>
						<a href="', $article['msg_link'], '">', $article['title'], '</a>', $article['is_new'] ? '
						<span class="new_posts">' . $txt['new'] . '</span> ' : '', '
					</h3>
					<div class="smalltext" style="opacity: .5">';

			if (!empty($article['replies']['num'])) {
				echo '
						<i class="fas fa-reply"></i>';
			}

			if (!empty($article['author']['id'])) {
				echo '
						<a href="', $article['author']['link'], '" title="', isset($txt['profile_of']) ? ($txt['profile_of'] . ' ' . $article['author']['name']) : (sprintf($txt['view_profile_of_username'], $article['author']['name'])), '">', $article['author']['name'], '</a>, ';
			} else {
				echo '
						', $article['author']['name'], ', ';
			}

			echo '
						<span', $context['lp_need_lower_case'] ? ' style="text-transform: lowercase"' : '', '>', $article['date'], '</span>
					</div>';

			if (!empty($article['teaser'])) {
				echo '
					<p style="margin-bottom: 5px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.4em">
						', $article['teaser'], '
					</p>';
			}

			echo '
				</div>
				<div class="floatright smalltext">';

			if (!empty($article['section']['name'])) {
				echo '
					<a class="new_posts" href="', $article['section']['link'], '">', $article['section']['name'], '</a>';
			}

			echo '
					<div class="righttext">
						<i class="fas fa-eye" title="', $article['views']['title'], '"></i> ', $article['views']['num'];

			if (!empty($article['replies']['num'])) {
				echo '
						<i class="fas fa-comment" title="', $article['replies']['title'], '"></i> ', $article['replies']['num'];
			}

			echo '
					</div>
				</div>
			</div>';
		}
	}

	echo '
		</div>';

	if (!empty($context['page_index']))
		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

	echo '
	</div>';
}
