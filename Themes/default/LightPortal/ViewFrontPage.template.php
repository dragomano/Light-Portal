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

/**
 * Example #1 of custom page view
 *
 * Пример #1 кастомного отображения страниц
 *
 * @return void
 */
function template_show_five_pages_as_articles()
{
	global $context, $scripturl, $txt, $modSettings;

	if (!empty($context['lp_frontpage_articles'])) {
		if (empty($context['lp_active_blocks']))
			echo '
	<div class="col-xs">';

		$pages = array_slice($context['lp_frontpage_articles'], 0, 5);
		$areas = [
			'1/1/3/3',
			'1/3',
			'1/4',
			'2/3',
			'2/4'
		];

		echo '
	<div class="lp_frontpage_articles" style="display: grid; margin-bottom: 2em; grid-gap: 2%; grid-template-rows: min-content; align-items: start; grid-template-columns: repeat(4, 1fr);">';

		foreach ($pages as $count => $page) {
			echo '
		<div style="grid-area: ', $areas[$count], empty($count) ? '; height: 100%' : '', '">
			<article class="card roundframe" style="height: 100%; margin: 0">
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
					<div class="card-img-hover" style="background-image: url(\'', $page['image'], '\')', empty($count) ? '; height: 100%; opacity: .3' : '', '"></div>
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

			if (empty($count)) {
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
 * Example #2 of custom page view
 *
 * Пример #2 кастомного отображения страниц
 *
 * @return void
 */
function template_alt_show_pages_as_articles()
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
			<article style="transition: all .4s cubic-bezier(.175, .885, 0, 1); position: relative; padding: 0; display: flex; flex-direction: column; justify-content: space-between; height: 96%">
				<div class="title_bar article_header" style="padding: 8px 12px">
					<h3 class="floatleft">
						<a href="', $page['link'], '">', $page['title'], '</a>', $page['is_new'] ? (' <span class="new_posts">' . $txt['new'] . '</span>') : '', '
					</h3>';

			if ($page['can_edit']) {
				echo '
					<span class="floatright">
						<a href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $page['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</span>';
			}

			echo '
				</div>
				<div class="article_body roundframe" style="overflow: hidden">';

			if (!empty($modSettings['lp_show_num_views_and_comments'])) {
				echo '
					<span class="floatleft">
						<i class="fas fa-eye" title="', $txt['views'], '"></i> ', $page['num_views'];

				if (!empty($page['num_comments'])) {
					echo '
						<i class="fas fa-comment" title="', $txt['lp_comments'], '"></i> ', $page['num_comments'];
				}

				echo '
					</span>';
			}

			if (!empty($page['image'])) {
				echo '
					<img src="' . $page['image'] . '" alt="">';
			}

			if (!empty($modSettings['lp_show_teaser'])) {
				echo '
					<p>', $page['teaser'], '</p>';
			}

			echo '
					<div class="article_footer">
						<div class="centertext" style="padding: 4px">
							<a class="bbc_link" href="', $page['link'], '">', $txt['lp_read_more'], '</a>
						</div>
						<div class="centertext" style="padding: 4px">
							<time datetime="', $page['datetime'], '">', $page['date'], '</time>';

			if (!empty($modSettings['lp_show_author'])) {
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
			}

			echo '
						</div>
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
