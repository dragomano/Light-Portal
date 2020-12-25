<?php

/**
 * Topics from selected boards as sources of articles
 *
 * Темы из выбранных разделов в виде статей
 *
 * @return void
 */
function template_show_topics_as_articles()
{
	global $context, $scripturl, $txt, $modSettings;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $topic) {
			echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_layout'], ' col-xl-', $context['lp_frontpage_layout'], '">
			<article class="card roundframe', $topic['css_class'], '">
				<div class="card-info-hover">';

			if ($topic['can_edit']) {
				echo '
					<div class="card-edit-icon">
						<a href="', $scripturl, '?action=post;msg=', $topic['id_msg'], ';topic=', $topic['id'], '.0">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
			}

			echo '
				</div>';

			if (!empty($topic['image'])) {
				echo '
				<div class="card-img" style="background-image: url(\'' . $topic['image'] . '\')"></div>
				<a href="', $topic['link'], '">
					<div class="card-img-hover" style="background-image: url(\'', $topic['image'], '\')"></div>
				</a>';
			}

			echo '
				<div class="card-info">
					<span class="card-date smalltext">
						', $topic['is_new'] ? ('<span class="new_posts">' . $txt['new'] . '</span>') : '', '
						<time datetime="', $topic['datetime'], '">', $topic['date'], '</time>
					</span>
					<h3 class="card-title">
						<a href="', $topic['msg_link'], '">', $topic['subject'], '</a>
					</h3>';

			if (!empty($modSettings['lp_show_teaser'])) {
				echo '
					<p>', $topic['teaser'], '</p>';
			}

			echo '
					<div>';

			if (!empty($modSettings['lp_show_author'])) {
				echo '
						<span class="card-by">';

				if (empty($modSettings['lp_frontpage_article_sorting']) && !empty($topic['num_replies'])) {
					echo '
							<i class="fas fa-reply"></i>';
				}

				if (!empty($topic['author_id']) && !empty($topic['author_name'])) {
					echo '
							<a href="', $topic['author_link'], '" class="card-author">', $topic['author_name'], '</a>';
				} else {
					echo '
							<span class="card-author">', $txt['guest_title'], '</span>';
				}

				echo '
						</span>';
			}

			if (!empty($modSettings['lp_show_num_views_and_comments'])) {
				echo '
						<span class="floatright">
							<i class="fas fa-eye" title="', $txt['views'], '"></i> ', $topic['num_views'];

				if (!empty($topic['num_replies'])) {
					echo '
							<i class="fas fa-comment" title="', $txt['replies'], '"></i> ', $topic['num_replies'];
				}

				echo '
						</span>';
			}

			echo '
					</div>
				</div>
			</article>
		</div>';
		}

		if (!empty($context['page_index']))
			echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		echo '
	</div>';
	} else {
		echo '
	<div class="infobox">', $txt['lp_no_items'], '</div>';
	}
}

/**
 * Pages as sources of articles
 *
 * Страницы в виде статей
 *
 * @return void
 */
function template_show_pages_as_articles()
{
	global $context, $scripturl, $txt, $modSettings;

	if (!empty($context['lp_frontpage_articles'])) {
		if (empty($context['lp_active_blocks']))
			echo '
	<div class="col-xs">';

		echo '
	<div class="lp_frontpage_articles row"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

		foreach ($context['lp_frontpage_articles'] as $page) {
			echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_layout'], ' col-xl-', $context['lp_frontpage_layout'], '">
			<article class="card roundframe">
				<div class="card-info-hover">';

			if ($page['can_edit']) {
				echo '
					<div class="card-edit-icon">
						<a href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $page['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
			}

			echo '
				</div>';

			if (!empty($page['image'])) {
				echo '
				<div class="card-img" style="background-image: url(\'' . $page['image'] . '\')"></div>
				<a href="', $page['link'], '">
					<div class="card-img-hover" style="background-image: url(\'', $page['image'], '\')"></div>
				</a>';
			}

			echo '
				<div class="card-info">
					<span class="card-date smalltext">
						', $page['is_new'] ? ('<span class="new_posts">' . $txt['new'] . '</span>') : '', '
						<time datetime="', $page['datetime'], '">', $page['date'], '</time>
					</span>
					<h3 class="card-title">
						<a href="', $page['link'], '">', $page['title'], '</a>
					</h3>';

			if (!empty($modSettings['lp_show_teaser'])) {
				echo '
					<p>', $page['teaser'], '</p>';
			}

			echo '
					<div>';

			if (!empty($modSettings['lp_show_author'])) {
				echo '

						<span class="card-by">';

				if (empty($modSettings['lp_frontpage_article_sorting']) && !empty($page['num_comments'])) {
					echo '
							<i class="fas fa-reply"></i>';
				}

				if (!empty($page['author_id']) && !empty($page['author_name'])) {
					echo '
							<a href="', $page['author_link'], '" class="card-author">', $page['author_name'], '</a>';
				} else {
					echo '
							<span class="card-author">', $txt['guest_title'], '</span>';
				}

				echo '
						</span>';
			}

			if (!empty($modSettings['lp_show_num_views_and_comments'])) {
				echo '
						<span class="floatright">
							<i class="fas fa-eye" title="', $txt['views'], '"></i> ', $page['num_views'];

				if (!empty($page['num_comments'])) {
					echo '
							<i class="fas fa-comment" title="', $txt['lp_comments'], '"></i> ', $page['num_comments'];
				}

				echo '
						</span>';
			}

			echo '
					</div>
				</div>
			</article>
		</div>';
		}

		if (!empty($context['page_index']))
			echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		echo '
	</div>';

		if (empty($context['lp_active_blocks']))
			echo '
	</div>';
	} else {
		echo '
	<div class="infobox">', $txt['lp_no_items'], '</div>';
	}
}

/**
 * Selected boards as sources of articles
 *
 * Выбранные разделы в виде статей
 *
 * @return void
 */
function template_show_boards_as_articles()
{
	global $context, $scripturl, $txt, $modSettings;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $board) {
			echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_layout'], ' col-xl-', $context['lp_frontpage_layout'], '">
			<article class="card roundframe">
				<div class="card-info-hover">';

			if ($board['can_edit']) {
				echo '
					<div class="card-edit-icon">
						<a href="', $scripturl, '?action=admin;area=manageboards;sa=board;boardid=', $board['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
			}

			echo '
				</div>';

			if (!empty($board['image'])) {
				echo '
				<div class="card-img" style="background-image: url(\'' . $board['image'] . '\')"></div>
				<a href="', $board['link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>
					<div class="card-img-hover" style="background-image: url(\'', $board['image'], '\')"></div>
				</a>';
			}

			echo '
				<div class="card-info">
					<span class="card-date smalltext">
						', $board['is_updated'] ? ('<span class="new_posts">' . $txt['new'] . '</span>') : '';

			if (!empty($board['date']))
				echo '
						<time datetime="', $board['datetime'], '">', $board['date'], '</time>';

			echo '
					</span>
					<h3 class="card-title">
						<a href="', $board['msg_link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>', $board['name'], '</a>
					</h3>';

			if (!empty($modSettings['lp_show_teaser'])) {
				echo '
					<p>', $board['teaser'], '</p>';
			}

			echo '
					<div>';

			if (!empty($modSettings['lp_show_author'])) {
				echo '
						<span class="card-by">
							<span class="card-author">', $board['category'], '</span>
						</span>';
			}

			if (!empty($modSettings['lp_show_num_views_and_comments'])) {
				echo '
						<span class="floatright">';

				if ($board['is_redirect']) {
					echo '<i class="fas fa-directions"></i>';
				} else {
					echo '<i class="fas fa-comment" title="', $txt['replies'], '"></i> ', $board['num_posts'];
				}

				echo '
						</span>';
			}

			echo '
					</div>
				</div>
			</article>
		</div>';
		}

		if (!empty($context['page_index']))
			echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';

		echo '
	</div>';
	} else {
		echo '
	<div class="infobox">', $txt['lp_no_items'], '</div>';
	}
}
