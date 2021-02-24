<?php

/**
 * Default template
 *
 * Шаблон по умолчанию
 *
 * @return void
 */
function template_empty()
{
	global $txt;

	echo '
	<div class="infobox">', $txt['lp_no_items'], '</div>';
}

/**
 * Wrong template
 *
 * Неверный шаблон
 *
 * @return void
 */
function template_wrong_template()
{
	global $txt;

	echo '
	<div class="errorbox">', $txt['lp_wrong_template'], '</div>';
}

/**
 * Topics from selected boards as sources of articles
 *
 * Темы из выбранных разделов в виде статей
 *
 * @return void
 */
function template_show_topics()
{
	global $context, $txt, $scripturl, $modSettings;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

	foreach ($context['lp_frontpage_articles'] as $topic) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe', $topic['css_class'], '">';

		if ($topic['is_new']) {
			echo '
				<div class="new_hover">
					<div class="new_icon">
						<span class="new_posts">', $txt['new'], '</span>
					</div>
				</div>';
		}

		echo '
				<div class="info_hover">';

		if ($topic['can_edit']) {
			echo '
					<div class="edit_icon">
						<a href="', $scripturl, '?action=post;msg=', $topic['id_msg'], ';topic=', $topic['id'], '.0">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
		}

		echo '
				</div>';

		if (!empty($topic['image'])) {
			echo '
				<div class="card_img"></div>
				<a href="', $topic['link'], '">
					<div class="card_img_hover" style="background-image: url(\'', $topic['image'], '\')"></div>
				</a>';
		}

		echo '
				<div class="card_info">
					<span class="card_date smalltext">';

		if (!empty($topic['board_name'])) {
			echo '
						<a class="floatleft" href="', $topic['board_link'], '"><i class="far fa-list-alt"></i> ', $topic['board_name'], '</a>';
		}

		echo '
						<time class="floatright" datetime="', $topic['datetime'], '"><i class="fas fa-clock"></i> ', $topic['date'], '</time>
					</span>
					<h3>
						<a href="', $topic['msg_link'], '">', $topic['subject'], '</a>
					</h3>';

		if (!empty($topic['teaser'])) {
			echo '
					<p>', $topic['teaser'], '</p>';
		}

		echo '
					<div>';

		if (!empty($modSettings['lp_show_author'])) {
			if (!empty($topic['author_id']) && !empty($topic['author_name'])) {
				echo '
						<a href="', $topic['author_link'], '" class="card_author"><i class="fas fa-user"></i> ', $topic['author_name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}
		}

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatright">
							<i class="fas fa-eye" title="', $txt['lp_views'], '"></i> ', $topic['num_views'];

			if (!empty($topic['num_replies'])) {
				echo '
							<i class="fas fa-comment" title="', $txt['lp_replies'], '"></i> ', $topic['num_replies'];
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
}

/**
 * Example of custom view for front topics
 *
 * Пример альтернативного отображения тем
 *
 * @return void
 */
function template_show_topics_alt()
{
	global $context, $txt, $modSettings, $scripturl;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_alt_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

	foreach ($context['lp_frontpage_articles'] as $topic) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe">
				<header>
					<div class="title_bar">
						<h3>
							<a href="', $topic['msg_link'], '">', $topic['subject'], '</a>', $topic['is_new'] ? (' <span class="new_posts">' . $txt['new'] . '</span>') : '', '
						</h3>
					</div>
					<div>';

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatleft">
							<i class="fas fa-eye" title="', $txt['lp_views'], '"></i> ', $topic['num_views'];

			if (!empty($topic['num_replies'])) {
				echo '
							<i class="fas fa-comment" title="', $txt['lp_replies'], '"></i> ', $topic['num_replies'];
			}

			echo '
						</span>';
		}

		if (!empty($topic['board_name'])) {
			echo '
						<a class="floatright" href="', $topic['board_link'], '"><i class="far fa-list-alt"></i> ', $topic['board_name'], '</a>';
		}

		echo '
					</div>';

		if (!empty($topic['image'])) {
			echo '
					<img src="', $topic['image'], '" alt="', $topic['subject'], '">';
		}

		echo '
				</header>
				<div class="article_body">';

		if (!empty($topic['teaser'])) {
			echo '
					<p>', $topic['teaser'], '</p>';
		}

		echo '
				</div>
				<div class="article_footer">
					<div class="centertext">
						<a class="bbc_link" href="', $topic['link'], '">', $txt['lp_read_more'], '</a>
					</div>
					<div class="centertext">
						<time datetime="', $topic['datetime'], '"><i class="fas fa-clock"></i> ', $topic['date'], '</time>';

		if (!empty($modSettings['lp_show_author'])) {
			if (!empty($topic['author_id']) && !empty($topic['author_name'])) {
				echo '
						| <i class="fas fa-user"></i> <a href="', $topic['author_link'], '" class="card_author">', $topic['author_name'], '</a>';
			} else {
				echo '
						| <span class="card_author">', $txt['guest_title'], '</span>';
			}
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
}

/**
 * Example of custom view for front topics
 *
 * Пример альтернативного отображения тем
 *
 * @return void
 */
function template_show_topics_alt2()
{
	global $context, $modSettings, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="topic_alt2_view">';

	foreach ($context['lp_frontpage_articles'] as $topic) {
		echo '
		<article class="descbox">';

		if (!empty($topic['image'])) {
			echo '
			<a class="article_image_link" href="', $topic['link'], '">
				<div style="background-image: url(\'' . $topic['image'] . '\')"></div>
			</a>';
		}

		echo '
			<div class="article_body">
				<div>
					<header>
						<time datetime="', $topic['datetime'], '"><i class="fas fa-clock"></i> ', $topic['date'], '</time>
						<h3><a href="', $topic['msg_link'], '">', $topic['subject'], '</a></h3>
					</header>';

		if (!empty($topic['teaser'])) {
			echo '
					<section>
						<p>', $topic['teaser'], '</p>
					</section>';
		}

		echo '
				</div>';

		if (!empty($modSettings['lp_show_author'])) {
			echo '
				<footer>';

			if (!empty($topic['author_avatar'])) {
				echo '
					<img src="', $topic['author_avatar'], '" loading="lazy" alt="', $txt['author'], '">';
			}

			echo '
					<span>';

			if (!empty($topic['author_id']) && !empty($topic['author_name'])) {
				echo '
						<a href="', $topic['author_link'], '">', $topic['author_name'], '</a>';
			} else {
				echo '
						<span>', $txt['guest_title'], '</span>';
			}

			echo '
					</span>
				</footer>';
		}

		echo '
			</div>
		</article>';
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
}

/**
 * Example of custom view for front topics
 *
 * Пример альтернативного отображения тем
 *
 * @return void
 */
function template_show_topics_alt3()
{
	global $context, $modSettings, $txt, $scripturl;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_alt3_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px; margin-left: 5px"' : '', '>';

	$i = 0;
	foreach ($context['lp_frontpage_articles'] as $topic) {
		$i++;

		echo '
		<div class="card', $i % 2 === 0 ? ' alt': '', ' col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">
			<div class="meta">';

		if (!empty($topic['image'])) {
			echo '
				<div class="photo" style="background-image: url(\'', $topic['image'], '\')"></div>';
		}

		echo '
				<ul class="details">';

		if (!empty($modSettings['lp_show_author'])) {
			echo '
					<li class="author">
						<i class="fas fa-user"></i>';

			if (!empty($topic['author_id']) && !empty($topic['author_name'])) {
				echo '
						<a href="', $topic['author_link'], '">', $topic['author_name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}

			echo '
					</li>';
		}

		echo '
					<li class="date"><i class="fas fa-calendar"></i><time datetime="', $topic['datetime'], '">', $topic['date'], '</time></li>';

		if (!empty($topic['keywords'])) {
			echo '
					<li class="tags">
						<i class="fas fa-tag"></i>
						<ul style="display: inline">';

			foreach ($topic['keywords'] as $id => $name) {
				echo '
							<li><a href="', $scripturl, '?action=keywords;id=', $id, '">', $name, '</a></li>';
			}

			echo '
						</ul>
					</li>';
		}

		echo '
				</ul>
			</div>
			<div class="description">
				<h1><a href="', $topic['link'], '">', $topic['subject'], '</a></h1>';

		if (!empty($topic['board_name'])) {
			echo '
				<h2><a href="', $topic['board_link'], '"><i class="far fa-list-alt"></i> ', $topic['board_name'], '</a></h2>';
		}

		if (!empty($topic['teaser'])) {
			echo '
				<p>', $topic['teaser'], '</p>';
		}

		echo '
				<p class="read_more">
					<a class="bbc_link" href="', $topic['msg_link'], '">', $txt['lp_read_more'], '</a> <i class="fas fa-arrow-right"></i>
				</p>
			</div>
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
}

/**
 * Example of custom view for front topics
 *
 * Пример альтернативного отображения тем
 *
 * @return void
 */
function template_show_topics_alt4()
{
	global $context, $scripturl;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_alt4_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

	foreach ($context['lp_frontpage_articles'] as $topic) {
		echo '
		<article>
			<div class="num"></div>
			<a class="card" href="', $topic['link'], '"', !empty($topic['image']) ? ' style="--bg-img: url(\'' . $topic['image'] . '\')"' : '', '>
				<div>
					<h1>', $topic['subject'], '</h1>';

		if (!empty($topic['teaser'])) {
			echo '
					<p>', $topic['teaser'], '</p>';
		}

		echo '
					<div class="date">
						<time datetime="', $topic['datetime'], '">', $topic['date'], '</time>
					</div>';

		if (!empty($topic['board_name'])) {
			echo '
					<div class="tags">
						<div class="tag">', $topic['board_name'], '</div>
					</div>';
		}

		echo '
				</div>
			</a>
		</article>';
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
}

/**
 * Pages as sources of articles
 *
 * Страницы в виде статей
 *
 * @return void
 */
function template_show_pages()
{
	global $context, $scripturl, $txt, $modSettings;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

	foreach ($context['lp_frontpage_articles'] as $page) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe">';

		if ($page['is_new']) {
			echo '
				<div class="new_hover">
					<div class="new_icon">
						<span class="new_posts">', $txt['new'], '</span>
					</div>
				</div>';
		}

		echo '
				<div class="info_hover">';

		if ($page['can_edit']) {
			echo '
					<div class="edit_icon">
						<a href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $page['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
		}

		echo '
				</div>';

		if (!empty($page['image'])) {
			echo '
				<div class="card_img"></div>
				<a href="', $page['link'], '">
					<div class="card_img_hover" style="background-image: url(\'', $page['image'], '\')"></div>
				</a>';
		}

		echo '
				<div class="card_info">
					<span class="card_date smalltext">';

		if (!empty($page['category_name'])) {
			echo '
						<a class="floatright" href="', $page['category_link'], '"><i class="far fa-list-alt"></i> ', $page['category_name'], '</a>';
		}

		echo '
						<time class="floatleft" datetime="', $page['datetime'], '"><i class="fas fa-clock"></i> ', $page['date'], '</time>
					</span>
					<h3>
						<a href="', $page['link'], '">', $page['title'], '</a>
					</h3>';

		if (!empty($page['teaser'])) {
			echo '
					<p>', $page['teaser'], '</p>';
		}

		echo '
					<div>';

		if (!empty($modSettings['lp_show_author'])) {
			if (!empty($page['author_id']) && !empty($page['author_name'])) {
				echo '
						<a href="', $page['author_link'], '" class="card_author"><i class="fas fa-user"></i> ', $page['author_name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}
		}

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatright">
							<i class="fas fa-eye" title="', $txt['lp_views'], '"></i> ', $page['num_views'];

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
}

/**
 * Example of custom view for front pages
 *
 * Пример альтернативного отображения страниц
 *
 * @return void
 */
function template_show_pages_alt()
{
	global $context, $txt, $modSettings, $scripturl;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_alt_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

	foreach ($context['lp_frontpage_articles'] as $page) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe">
				<header>
					<div class="title_bar">
						<h3>
							<a href="', $page['link'], '">', $page['title'], '</a>', $page['is_new'] ? (' <span class="new_posts">' . $txt['new'] . '</span>') : '', '
						</h3>
					</div>
					<div>';

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatleft">
							<i class="fas fa-eye" title="', $txt['lp_views'], '"></i> ', $page['num_views'];

			if (!empty($page['num_comments'])) {
				echo '
							<i class="fas fa-comment" title="', $txt['lp_comments'], '"></i> ', $page['num_comments'];
			}

			echo '
						</span>';
		}

		if (!empty($page['category_name'])) {
			echo '
						<a class="floatright" href="', $page['category_link'], '"><i class="far fa-list-alt"></i> ', $page['category_name'], '</a>';
		}

		echo '
					</div>';

		if (!empty($page['image'])) {
			echo '
					<img src="', $page['image'], '" alt="', $page['title'], '">';
		}

		echo '
				</header>
				<div class="article_body">';

		if (!empty($page['teaser'])) {
			echo '
					<p>', $page['teaser'], '</p>';
		}

		echo '
				</div>
				<div class="article_footer">
					<div class="centertext">
						<a class="bbc_link" href="', $page['link'], '">', $txt['lp_read_more'], '</a>
					</div>
					<div class="centertext">
						<time datetime="', $page['datetime'], '"><i class="fas fa-clock"></i> ', $page['date'], '</time>';

		if (!empty($modSettings['lp_show_author'])) {
			if (!empty($page['author_id']) && !empty($page['author_name'])) {
				echo '
						| <i class="fas fa-user"></i> <a href="', $page['author_link'], '" class="card_author">', $page['author_name'], '</a>';
			} else {
				echo '
						| <span class="card_author">', $txt['guest_title'], '</span>';
			}
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
}

/**
 * Selected boards as sources of articles
 *
 * Выбранные разделы в виде статей
 *
 * @return void
 */
function template_show_boards()
{
	global $context, $scripturl, $txt, $modSettings;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles row topic_view"', !empty($context['lp_active_blocks']) ? ' style="margin-top: -10px"' : '', '>';

	foreach ($context['lp_frontpage_articles'] as $board) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-', $context['lp_frontpage_num_columns'], ' col-xl-', $context['lp_frontpage_num_columns'], '">
			<article class="card roundframe">';

		if ($board['is_updated']) {
			echo '
				<div class="new_hover">
					<div class="new_icon">
						<span class="new_posts">', $txt['new'], '</span>
					</div>
				</div>';
		}

		echo '
				<div class="info_hover">';

		if ($board['can_edit']) {
			echo '
					<div class="edit_icon">
						<a href="', $scripturl, '?action=admin;area=manageboards;sa=board;boardid=', $board['id'], '">
							<i class="fas fa-edit" title="', $txt['edit'], '"></i>
						</a>
					</div>';
		}

		echo '
				</div>';

		if (!empty($board['image'])) {
			echo '
				<div class="card_img"></div>
				<a href="', $board['link'], '">
					<div class="card_img_hover" style="background-image: url(\'', $board['image'], '\')"></div>
				</a>';
		}

		echo '
				<div class="card_info">
					<span class="card_date smalltext">';

		if (!empty($board['date']))
			echo '
						<time datetime="', $board['datetime'], '"><i class="fas fa-clock"></i> ', $board['date'], '</time>';

		echo '
					</span>
					<h3 class="card-title">
						<a href="', $board['msg_link'], '">', $board['name'], '</a>
					</h3>';

		if (!empty($board['teaser'])) {
			echo '
					<p>', $board['teaser'], '</p>';
		}

		echo '
					<div>';

		if (!empty($modSettings['lp_show_author'])) {
			echo '
						<span class="card-by">
							<span class="card_author"><i class="fas fa-list-alt"></i> ', $board['category'], '</span>
						</span>';
		}

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatright">';

			if ($board['is_redirect']) {
				echo '<i class="fas fa-directions"></i>';
			} else {
				echo '<i class="fas fa-comment" title="', $txt['lp_replies'], '"></i> ', $board['num_posts'];
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
}

/**
 * Шаблон списка сортировки для страниц рубрик и тегов
 *
 * Template of sort list for category pages and tags
 *
 * @return void
 */
function template_sorting_above()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>';

	if (empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="information">', $txt['lp_no_items'], '</div>';
	} else {
		echo '
	<div class="information">
		<div class="floatright">
			<form action="', $context['canonical_url'], '" method="post">
				<label for="sort">', $txt['lp_sorting_label'], '</label>
				<select id="sort" name="sort" onchange="this.form.submit()">
					<option value="title;desc"', $context['current_sorting'] == 'title;desc' ? ' selected' : '', '>', $txt['lp_sort_by_title_desc'], '</option>
					<option value="title"', $context['current_sorting'] == 'title' ? ' selected' : '', '>', $txt['lp_sort_by_title'], '</option>
					<option value="created;desc"', $context['current_sorting'] == 'created;desc' ? ' selected' : '', '>', $txt['lp_sort_by_created_desc'], '</option>
					<option value="created"', $context['current_sorting'] == 'created' ? ' selected' : '', '>', $txt['lp_sort_by_created'], '</option>
					<option value="updated;desc"', $context['current_sorting'] == 'updated;desc' ? ' selected' : '', '>', $txt['lp_sort_by_updated_desc'], '</option>
					<option value="updated"', $context['current_sorting'] == 'updated' ? ' selected' : '', '>', $txt['lp_sort_by_updated'], '</option>
					<option value="author_name;desc"', $context['current_sorting'] == 'author_name;desc' ? ' selected' : '', '>', $txt['lp_sort_by_author_desc'], '</option>
					<option value="author_name"', $context['current_sorting'] == 'author_name' ? ' selected' : '', '>', $txt['lp_sort_by_author'], '</option>
					<option value="num_views;desc"', $context['current_sorting'] == 'num_views;desc' ? ' selected' : '', '>', $txt['lp_sort_by_num_views_desc'], '</option>
					<option value="num_views"', $context['current_sorting'] == 'num_views' ? ' selected' : '', '>', $txt['lp_sort_by_num_views'], '</option>
				</select>
			</form>
		</div>
	</div>';
	}
}

function template_sorting_below()
{
}
