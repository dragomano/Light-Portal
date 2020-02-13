<?php

/**
 * The portal page template
 *
 * Шаблон страницы портала
 *
 * @return void
 */
function template_show_page()
{
	global $context, $scripturl, $txt, $settings, $modSettings, $boardurl;

	echo '
	<section itemscope itemtype="http://schema.org/Article">
		<div id="display_head" class="windowbg">
			<h2 class="display_title" itemprop="headline">
				<span id="top_subject">', $context['page_title'], $context['lp_page']['can_edit'] ? '<a class="floatright" href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '</span>
			</h2>';

	if (!empty($context['lp_page']['options']['show_author_and_date'])) {
		echo '
			<p>
				<span class="floatleft"><i class="fas fa-user" aria-hidden="true"></i> <span itemprop="author">', $context['lp_page']['author'], '</span></span>
				<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
					<i class="fas fa-clock" aria-hidden="true"></i> ', $context['lp_page']['created'], !empty($context['lp_page']['updated_at']) ? ' <meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '"><i class="fas fa-user-edit" aria-hidden="true"></i> ' . $context['lp_page']['updated'] : '', '
				</time>
			</p>';
	}

	echo '
			<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', $context['canonical_url'], '" content="', $context['canonical_url'], '">
			<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
				<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
					<img itemprop="url image" src="', $context['header_logo_url_html_safe'] ?: ($settings['images_url'] . '/thumbnail.png'), '" style="display:none" alt="">
				</span>
				<meta itemprop="name" content="', $context['forum_name_html_safe'], '">
				<meta itemprop="address" content="', !empty($modSettings['lp_page_itemprop_address']) ? $modSettings['lp_page_itemprop_address'] : $boardurl, '">
				<meta itemprop="telephone" content="', !empty($modSettings['lp_page_itemprop_phone']) ? $modSettings['lp_page_itemprop_phone'] : '', '">
			</span>
		</div>
		<article class="roundframe" itemprop="articleBody">
			<h3 style="display: none;">', $context['lp_page']['author'], ' - ', $context['page_title'], '</h3>';

	if (!empty($context['lp_page']['keywords']) && !empty($modSettings['lp_show_tags_on_page'])) {
		echo '
			<div class="smalltext">';

		foreach (explode(', ', $context['lp_page']['keywords']) as $keyword) {
			echo '
				<a class="button" href="', $scripturl, '?action=portal;sa=tags;key=', urlencode($keyword), '">', $keyword, '</a>';
		}

		echo '
			</div><hr>';
	}

	if (!empty($settings['og_image']))
		echo '
			<meta itemprop="image" content="', $settings['og_image'], '">';

	echo '
			<div class="page_', $context['lp_page']['type'], '">', $context['lp_page']['content'], '</div>
		</article>';

	show_comment_block();

	echo '
	</section>';
}

/**
 * Comment block template
 *
 * Шаблон блока комментариев
 *
 * @return void
 */
function show_comment_block()
{
	global $context, $modSettings, $txt, $settings, $options;

	if (empty($context['lp_page']['options']['allow_comments']) || empty($modSettings['lp_show_comment_block']))
		return;

	if (!empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'])) {
		echo $context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'];
		return;
	}

	if ($modSettings['lp_show_comment_block'] !== 'default')
		return;

	echo '
		<aside class="comments">
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="page_comments_toggle" class="fa toggle_', empty($options['collapse_header_page_comments']) ? 'up' : 'down', ' floatright fa-lg" style="display: none;"></span>
					<a id="page_comments_link">', $txt['lp_comments'], '</a>
				</h3>
			</div>
			<div id="page_comments"', empty($options['collapse_header_page_comments']) ? '' : ' style="display: none;"', '>';

	if ($context['page_info']['num_pages'] > 1)
		echo '
				<div class="centertext">
					<div class="pagesection">
						<div class="pagelinks">', $context['page_index'], '</div>
					</div>
				</div>';

	$i = 0;
	if (!empty($context['lp_page']['comments'])) {
		echo '
				<ul class="comment_list row">';

		foreach ($context['lp_page']['comments'] as $comment)
			show_single_comment($comment, $i++);

		echo '
				</ul>';
	}

	if ($context['page_info']['num_pages'] > 1)
		echo '
				<div class="centertext">
					<div class="pagesection">
						<div class="pagelinks">', $context['page_index'], '</div>
					</div>
				</div>';

	if ($context['user']['is_logged'])
		echo '
				<form id="comment_form" class="roundframe sceditor-container descbox" action="', $context['canonical_url'], $context['lp_page']['alias'] == '/' ? '?' : ';', 'new_comment" method="post" accept-charset="', $context['character_set'], '">
					<textarea id="message" name="message" class="content" cols="20" rows="5" placeholder="', $txt['lp_comment_placeholder'], '" required></textarea>
					<input type="hidden" name="parent_id" value="0">
					<input type="hidden" name="counter" value="0">
					<input type="hidden" name="level" value="1">
					<input type="hidden" name="page_id" value="', $context['lp_page']['id'], '">
					<input type="hidden" name="page_title" value="', $context['lp_page']['title'], '">
					<input type="hidden" name="page_url" value="', $context['canonical_url'], $context['lp_page']['alias'] == '/' ? '?' : ';', '">
					<input type="hidden" name="start" value="', (int) $_REQUEST['start'], '">
					<button type="submit" class="button" name="comment" disabled>', $txt['post'], '</button>
				</form>';

	echo '
			</div>
		</aside>';

	if ($context['user']['is_logged'])
		echo '
		<script>
			let canonical_url = "', $context['canonical_url'], '",
				confirm_text = ', JavaScriptEscape($txt['quickmod_confirm']), ',
				comment_redirect_url = canonical_url + "', $context['lp_page']['alias'] == '/' ? '?' : ';', '";
		</script>
		<script src="', $settings['default_theme_url'], '/scripts/light_portal/page_comments.js"></script>';

	echo '
		<script>
			let is_currently_collapsed = ', empty($options['collapse_header_page_comments']) ? 'false' : 'true', ',
				toggle_alt_expanded_title = ', JavaScriptEscape($txt['hide']), ',
				toggle_alt_collapsed_title = ', JavaScriptEscape($txt['show']), ',
				toggle_msg_block_title = ', JavaScriptEscape($txt['lp_comments']), ',
				use_theme_settings = ', $context['user']['is_guest'] ? 'false' : 'true', ',
				use_cookie = ', $context['user']['is_guest'] ? 'true' : 'false', ';
		</script>
		<script src="', $settings['default_theme_url'], '/scripts/light_portal/page_comments_toggle.js"></script>';
}

/**
 * Single comment template
 *
 * Шаблон одиночного комментария
 *
 * @param array $comment
 * @param int $i
 * @param int $level
 * @return void
 */
function show_single_comment($comment, $i = 0, $level = 1)
{
	global $context, $txt;

	echo '
	<li id="comment', $comment['id'], '" class="col-xs-12 generic_list_wrapper bg ', $i % 2 == 0 ? 'even' : 'odd', '" data-id="', $comment['id'], '" data-counter="', $i, '" data-level="', $level, '" itemprop="comment" itemscope="itemscope" itemtype="http://schema.org/Comment" style="list-style: none">
		<div class="comment_avatar">
			', $comment['avatar'];

	if (!empty($context['lp_page']['author_id']) && $context['lp_page']['author_id'] == $comment['author_id'])
		echo '
			<span class="new_posts">', $txt['author'], '</span>';

	echo '
		</div>
		<div class="comment_wrapper">
			<div class="comment_body">
				<div class="generic_list_wrapper popover_title">
					<span class="bold_text" itemprop="creator"', $context['user']['is_logged'] ? ' style="cursor: pointer"' : '', '>', $comment['author_name'], '</span>
					<span class="comment_date floatright" itemprop="datePublished" content="' , $comment['created_at'], '">', $comment['created'], ' <a href="#comment', $comment['id'], '">#' , $comment['id'], '</a></span>
				</div>
				<div class="content bg ', $i % 2 == 0 ? 'odd' : 'even', '" itemprop="text"', $context['user']['is_guest'] || $level >= 5 ? ' style="min-height: 4em"' : '', '>', $comment['message'], '</div>';

	if ($context['user']['is_logged'] && $level < 5) {
		echo '
				<div class="content bg ', $i % 2 == 0 ? 'odd' : 'even', ' smalltext" style="overflow: auto">
					<span class="button reply_button">', $txt['reply'], '</span>';

		// Only comment author or admin can remove comments
		if ($comment['author_id'] == $context['user']['id'] || $context['user']['is_admin'])
			echo '
					<span class="button remove_button floatright">', $txt['remove'], '</span>';

		echo '
				</div>';
	}

	echo '
			</div>';

	if (!empty($comment['childs'])) {
		echo '
			<ul class="comment_list row">';

		foreach ($comment['childs'] as $children_comment)
			show_single_comment($children_comment, $i + 1, $level + 1);

		echo '
			</ul>';
	}

	echo '
		</div>
	</li>';
}
