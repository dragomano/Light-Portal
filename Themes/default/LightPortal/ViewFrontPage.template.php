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
	global $context, $scripturl, $txt;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $topic) {
			$alt = $topic['subject'];

			echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-', $context['lp_frontpage_layout'], '">
			<article class="card roundframe', $topic['css_class'], '">
				<div class="card__info-hover">';

			if ($topic['can_edit']) {
				echo '
					<div class="card__edit-icon">
						<a href="', $scripturl, '?action=post;msg=', $topic['id_msg'], ';topic=', $topic['id'], '.0">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
			}

			echo '
				</div>';

			if (!empty($topic['image'])) {
				echo '
				<div class="card__img" style="background-image: url(\'' . $topic['image'] . '\')"></div>
				<a href="', $topic['link'], '">
					<div class="card__img--hover" style="background-image: url(\'', $topic['image'], '\')"></div>
				</a>';
			}

			echo '
				<div class="card__info">
					<span class="card__category smalltext">
						', $topic['is_new'] ? ('<span class="new_posts">' . $txt['new'] . '</span>') : '', '
						<time datetime="', $topic['datetime'], '">', $topic['date'], '</time>
					</span>
					<h3 class="card__title">
						<a href="', $topic['link'], '">', $topic['subject'], '</a>
					</h3>
					<div>
						<span class="card__by">';

			if (!empty($topic['author_id']) && !empty($topic['author_name'])) {
				echo '
							<a href="', $topic['author_link'], '" class="card__author">', $topic['author_name'], '</a>';
			} else {
				echo '
							<span class="card__author">', $txt['guest_title'], '</span>';
			}

			echo '
						</span>
						<span>
							<i class="fas fa-eye"></i> ', $topic['num_views'];

			if (!empty($topic['num_replies'])) {
				echo '
							<i class="fas fa-comment"></i> ', $topic['num_replies'];
			}

			echo '
						</span>
					</div>
				</div>
			</article>
		</div>';
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';
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
	global $context, $scripturl, $txt;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $page) {
			$alt = $page['title'];

			echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-', $context['lp_frontpage_layout'], '">
			<article class="card roundframe">
				<div class="card__info-hover">';

			if ($page['can_edit']) {
				echo '
					<div class="card__edit-icon">
						<a href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $page['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
			}

			echo '
				</div>';

			if (!empty($page['image'])) {
				echo '
				<div class="card__img" style="background-image: url(\'' . $page['image'] . '\')"></div>
				<a href="', $page['link'], '">
					<div class="card__img--hover" style="background-image: url(\'', $page['image'], '\')"></div>
				</a>';
			}

			echo '
				<div class="card__info">
					<span class="card__category smalltext">
						', $page['is_new'] ? ('<span class="new_posts">' . $txt['new'] . '</span>') : '', '
						<time datetime="', $page['datetime'], '">', $page['date'], '</time>
					</span>
					<h3 class="card__title">
						<a href="', $page['link'], '">', $page['title'], '</a>
					</h3>
					<div>
						<span class="card__by">';

			if (!empty($page['author_id']) && !empty($page['author_name'])) {
				echo '
							<a href="', $page['author_link'], '" class="card__author">', $page['author_name'], '</a>';
			} else {
				echo '
							<span class="card__author">', $txt['guest_title'], '</span>';
			}

			echo '
						</span>
						<span>
							<i class="fas fa-eye"></i> ', $page['num_views'];

			if (!empty($page['num_comments'])) {
				echo '
							<i class="fas fa-comment"></i> ', $page['num_comments'];
			}

			echo '
						</span>
					</div>
				</div>
			</article>
		</div>';
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';
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
	global $context, $scripturl, $txt;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $board) {
			$alt = $board['name'];

			echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-', $context['lp_frontpage_layout'], '">
			<article class="card roundframe">
				<div class="card__info-hover">';

			if ($board['can_edit']) {
				echo '
					<div class="card__edit-icon">
						<a href="', $scripturl, '?action=admin;area=manageboards;sa=board;boardid=', $board['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
			}

			echo '
				</div>';

			if (!empty($board['image'])) {
				echo '
				<div class="card__img" style="background-image: url(\'' . $board['image'] . '\')"></div>
				<a href="', $board['link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>
					<div class="card__img--hover" style="background-image: url(\'', $board['image'], '\')"></div>
				</a>';
			}

			echo '
				<div class="card__info">
					<span class="card__category smalltext">
						', $board['is_updated'] ? ('<span class="new_posts">' . $txt['new'] . '</span>') : '';

			if (!empty($board['date']))
				echo '
						<time datetime="', $board['datetime'], '">', $board['date'], '</time>';

			echo '
					</span>
					<h3 class="card__title">
						<a href="', $board['link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>', $board['name'], '</a>
					</h3>
					<div>
						<span class="card__by">
							<span class="card__author">', $board['category'], '</span>
						</span>
						<span>';

			if ($board['is_redirect']) {
				echo '<i class="fas fa-directions"></i>';
			} else {
				echo '<i class="fas fa-comment"></i> ', $board['num_posts'];
			}

			echo '
						</span>
					</div>
				</div>
			</article>
		</div>';
		}

		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';
	}
}

/**
 * Topics from selected boards as sources of articles (пользовательский шаблон)
 *
 * Темы из выбранных разделов в виде статей (пользовательский шаблон)
 *
 * @return void
 */
function template_show_topics_as_custom_style()
{
	global $scripturl, $context, $txt;

	echo '
	<div class="row">
		<div class="col-xs-12 col-sm-3 col-md-3">
			<nav>
				<ul>
					<li>
						<div class="roundframe" style="margin-top: 0">
							<ul>
								<li>
									<i class="far fa-comments"></i> <a href="', $scripturl, '?action=forum">Все разделы</a>
								</li>
								<li>
									<i class="fas fa-th-large"></i> <a href="', $scripturl, '?action=portal;sa=tags">Теги</a>
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
			</nav>
		</div>
		<div class="col-xs-12 col-sm-8 col-md-9">
			<div class="title_bar" style="margin-bottom: 1px">
				<h2 class="titlebg">', $context['page_title'], '</h2>
			</div>';

	if (!empty($context['lp_frontpage_articles'])) {
		foreach ($context['lp_frontpage_articles'] as $topic) {
			echo '
			<div class="windowbg', $topic['css_class'], '">';

			if (!empty($topic['image'])) {
				echo '
				<div class="floatleft" style="width: 64px">
					<img src="', $topic['image'], '" alt="', $topic['subject'], '">
				</div>';
				} elseif (!empty($topic['image_placeholder'])) {
					echo '
				<div class="floatleft" style="width: 64px">
					<i class="far fa-image fa-5x"></i>
				</div>';
			}

			echo '
				<div class="floatleft" style="margin-left: 20px; width: 70%">
					<h3>
						<a href="', $topic['link'], '">', $topic['subject'], '</a>', $topic['is_new'] ? '
						 <span class="new_posts">' . $txt['new'] . '</span> ' : '', '
					</h3>
					<div class="smalltext" style="opacity: .5">';

			if (!empty($topic['author_id'])) {
				echo '
						<a href="', $topic['author_link'], '" title="', $txt['profile_of'], ' ', $topic['author_name'], '">', $topic['author_name'], '</a>, ';
			} else
				echo $topic['author_name'], ', ';

			echo $topic['date'], '
					</div>';

			if (!empty($topic['teaser'])) {
				echo '
					<p>
						', $topic['teaser'], '
					</p>';
			}

			echo '
				</div>
				<div class="floatright smalltext">
					<a class="new_posts" href="', $topic['board_link'], '">', $topic['board_name'], '</a>
					<div class="righttext">
						<i class="fas fa-eye"></i> ', $topic['num_views'];

			if (!empty($topic['num_replies'])) {
				echo '
						<i class="fas fa-comment"></i> ', $topic['num_replies'];
			}

			echo '
					</div>
				</div>
			</div>';
		}
	}

	echo '
		</div>
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';
}
