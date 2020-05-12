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
	global $context, $txt;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $topic) {
			echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], ' col-lg-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $topic['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '',
					'<a data-id="', $topic['id'], '" href="', $topic['link'], '">', $topic['subject'], '</a>', '
				</h3>
			</div>
			<div class="roundframe noup', $topic['css_class'], '">';

			if (!empty($topic['image'])) {
				echo '
				<div class="article_image">
					<img src="', $topic['image'], '" alt="', $topic['subject'], '">
				</div>';
			} elseif (!empty($topic['image_placeholder'])) {
				echo '
				<div class="article_image">
					', $topic['image_placeholder'], '
				</div>';
			}

			echo '
				<div>
					<div class="floatleft">';

			if (!empty($topic['poster_id'])) {
				echo '
						<a href="', $topic['poster_link'], '" title="', $txt['profile_of'], ' ', $topic['poster_name'], '">', $topic['poster_name'], '</a>';
			} else
				echo $topic['poster_name'];

			echo '
					</div>
					<div class="floatright">
						<a href="', $topic['board_link'], '">', $topic['board_name'], '</a>
					</div>
					<div class="smalltext clear">
						<div class="floatleft">', $topic['time'], '</div>
						<div class="floatright">
							<i class="fas fa-eye"></i> ', $topic['num_views'];

			if (!empty($topic['num_replies'])) {
				echo '
							<i class="fas fa-comment"></i> ', $topic['num_replies'];
			}

			echo '
						</div>
					</div>
				</div>';

			if (!empty($topic['preview'])) {
				echo '
				<div class="article_content">
					<p class="inner">', $topic['preview'], '</p>
				</div>';
			}

			echo '
				<div class="centertext">
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
	global $context, $txt, $scripturl;

	if (!empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="lp_frontpage_articles row">';

		foreach ($context['lp_frontpage_articles'] as $page) {
			echo '
		<div class="col-xs-12 col-sm-', $context['lp_frontpage_layout'], ' col-md-', $context['lp_frontpage_layout'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $page['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a href="', $page['link'], '">', $page['title'], '</a>';

			if ($page['can_edit']) {
				echo '
					<a class="floatright" href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $page['id'] . '">
						<i class="fas fa-edit" title="' . $txt['edit'] . '"></i>
					</a>';
			}

			echo '
				</h3>
			</div>
			<div class="roundframe noup">';

			if (!empty($page['image'])) {
				echo '
				<div class="article_image">
					<img src="', $page['image'], '" alt="', $page['title'], '">
				</div>';
			} elseif (!empty($page['image_placeholder'])) {
				echo '
				<div class="article_image">
					', $page['image_placeholder'], '
				</div>';
			}

			echo '
				<div>
					<div class="floatleft">';

			if (!empty($page['author_name'])) {
				echo '
						<a href="' . $page['author_link'] . '" title="' . $txt['profile_of'] . ' ' . $page['author_name'] . '">' . $page['author_name'] . '</a>';
			} else
				echo $txt['guest_title'];

			echo '
					</div>
					<div class="smalltext clear">
						<div class="floatleft">', $page['created_at'], '</div>
						<div class="floatright">
							<i class="fas fa-eye"></i> ', $page['num_views'];

			if (!empty($page['num_comments'])) {
				echo '
							<i class="fas fa-comment"></i> ', $page['num_comments'];
			}

			echo '
						</div>
					</div>
				</div>';

			if (!empty($page['description'])) {
				echo '
				<div class="article_content page_', $page['type'], '">
					<p class="inner">', $page['description'], '</p>
				</div>';
			}

			echo '
				<div class="centertext">
					<a class="bbc_link" href="', $page['link'], '">', $txt['lp_read_more'], '</a>
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

			if (!empty($board['image'])) {
				echo '
				<div class="article_image">
					<img src="', $board['image'], '" alt="', $board['name'], '">
				</div>';
			} elseif (!empty($board['image_placeholder'])) {
				echo '
				<div class="article_image">
					', $board['image_placeholder'], '
				</div>';
			}

			echo '
				<div>
					<div class="floatleft">&nbsp;</div>
					<div class="floatright">', $board['category'], '</div>
					<div class="smalltext clear">
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

			if (!empty($board['description'])) {
				echo '
				<div class="article_content">
					<p class="inner">', $board['description'], '</p>
				</div>';
			}

			echo '
				<div class="centertext">
					<a class="bbc_link" href="', $board['last_post'] ?? $board['link'], '"', $board['is_redirect'] ? ' rel="nofollow noopener"' : '', '>
						', $board['is_redirect'] ? $txt['go_caps'] : $txt['lp_read_more'], '
					</a>
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
						<div class="roundframe">
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
						<a data-id="', $topic['id'], '" href="', $topic['link'], '">', $topic['subject'], '</a>', $topic['is_new'] ? '
						 <span class="new_posts">' . $txt['new'] . '</span> ' : '', '
					</h3>
					<div class="smalltext" style="opacity: .5">';

			if (!empty($topic['poster_id'])) {
				echo '
						<a href="', $topic['poster_link'], '" title="', $txt['profile_of'], ' ', $topic['poster_name'], '">', $topic['poster_name'], '</a>, ';
			} else
				echo $topic['poster_name'], ', ';

			echo '
						', $topic['time'], '
					</div>';

			if (!empty($topic['preview'])) {
				echo '
					<p>
						', $topic['preview'], '
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
