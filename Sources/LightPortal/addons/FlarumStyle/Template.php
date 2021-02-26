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
							<i class="far fa-comments"></i> <a href="', $scripturl, '?action=forum">', $txt['lp_flarum_style_addon_all_boards'], '</a>
						</li>
						<li>
							<i class="fas fa-th-large"></i> <a href="', $scripturl, '?action=keywords">', $txt['lp_flarum_style_addon_tags'], '</a>
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
									<i class="fas fa-chevron-circle-right"></i> <a href="', $scripturl, '?board=', $board['id'], '.0">', $board['name'], '</a>
								</li>
							</ul>';
				} else {
					echo '
							<i class="far fa-circle"></i> <a href="', $scripturl, '?board=', $board['id'], '.0">', $board['name'], '</a>';
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
 * Topics from selected boards as sources of articles (Flarum style)
 *
 * Темы из выбранных разделов в виде статей
 *
 * @return void
 */
function template_show_topics_as_flarum_style()
{
	global $context, $txt;

	echo '
	<div class="row">
		<div class="col-xs-12">
			<div class="title_bar" style="margin-bottom: 1px">
				<h2 class="titlebg">', $context['page_title'], '</h2>
			</div>';

	if (!empty($context['lp_frontpage_articles'])) {
		foreach ($context['lp_frontpage_articles'] as $topic) {
			echo '
			<div class="windowbg', $topic['css_class'], '">';

			if (!empty($topic['image'])) {
				echo '
				<div class="floatleft">
					<img class="avatar" src="', $topic['image'], '" alt="', $topic['title'], '" loading="lazy">
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
						<a href="', $topic['msg_link'], '">', $topic['title'], '</a>', $topic['is_new'] ? '
						<span class="new_posts">' . $txt['new'] . '</span> ' : '', '
					</h3>
					<div class="smalltext" style="opacity: .5">';

			if (!empty($topic['replies']['num'])) {
				echo '
						<i class="fas fa-reply"></i>';
			}

			if (!empty($topic['author']['id'])) {
				echo '
						<a href="', $topic['author']['link'], '" title="', $txt['profile_of'], ' ', $topic['author']['name'], '">', $topic['author']['name'], '</a>, ';
			} else {
				echo '
						', $topic['author']['name'], ', ';
			}

			echo '
						<span', $context['lp_need_lower_case'] ? ' style="text-transform: lowercase"' : '', '>', $topic['date'], '</span>
					</div>';

			if (!empty($topic['teaser'])) {
				echo '
					<p style="margin-bottom: 5px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.4em">
						', $topic['teaser'], '
					</p>';
			}

			echo '
				</div>
				<div class="floatright smalltext">
					<a class="new_posts" href="', $topic['section']['link'], '">', $topic['section']['name'], '</a>
					<div class="righttext">
						<i class="fas fa-eye" title="', $topic['views']['title'], '"></i> ', $topic['views']['num'];

			if (!empty($topic['replies']['num'])) {
				echo '
						<i class="fas fa-comment" title="', $topic['replies']['title'], '"></i> ', $topic['replies']['num'];
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
