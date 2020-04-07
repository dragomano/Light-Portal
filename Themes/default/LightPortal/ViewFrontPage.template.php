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
					', $topic['is_new'] ? '<span class="new_posts centericon">' . $txt['new'] . '</span> ' : '', '<a data-id="', $topic['id'], '" href="', $topic['link'], '">', $topic['subject'], '</a>', '
				</h3>
			</div>
			<div class="roundframe noup', $topic['css_class'], '">';

			if (!empty($topic['image'])) {
				echo '
				<img class="article_image" src="', $topic['image'], '" alt="', $topic['subject'], '">';
			} elseif (!empty($topic['image_placeholder'])) {
				echo '
				<span class="centertext">', $topic['image_placeholder'], '</span>';
			}

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
				<div class="article_content post">
					<p class="inner">', $topic['preview'], '</p>
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
			if ($page['can_show']) {
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
				<img class="article_image" src="', $page['image'], '" alt="', $page['title'], '">';
				} elseif (!empty($page['image_placeholder'])) {
					echo '
				<span class="centertext">', $page['image_placeholder'], '</span>';
				}

				echo '
				<div class="article_info">
					<div class="author_and_category">
						<div class="floatleft">&nbsp;</div>
						<div class="floatright">';

				if (!empty($page['author_id'])) {
					echo '
							<a href="' . $page['author_link'] . '" title="' . $txt['profile_of'] . ' ' . $page['author_name'] . '">' . $page['author_name'] . '</a>';
				} else
					echo $txt['guest_title'];

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
				<div class="article_content post page_', $page['type'], '">
					<p class="inner">', $page['description'], '</p>
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
				<img class="article_image" src="', $board['image'], '" alt="', $board['name'], '">';
			} elseif (!empty($board['image_placeholder'])) {
				echo '
				<span class="centertext">', $board['image_placeholder'], '</span>';
			}

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
				<div class="article_content post">
					<p class="inner">', $board['description'], '</p>
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
	}
}
