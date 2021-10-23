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
	global $context, $modSettings, $txt, $scripturl, $settings, $boardurl;

	if (!empty($context['lp_page']['errors'])) {
		echo '
	<aside class="errorbox">
		<ul>';

		$context['lp_page']['errors'] = array_unique($context['lp_page']['errors']);
		foreach ($context['lp_page']['errors'] as $error) {
			echo '
			<li><strong>', $error, '</strong></li>';
		}

		echo '
		</ul>
	</aside>';
	}

	if (!empty($modSettings['lp_show_page_permissions'])) {
		if ($context['lp_page']['can_edit']) {
			echo '
	<aside class="infobox">
		<strong>', $txt['edit_permissions'], '</strong>: ', $txt['lp_permissions'][$context['lp_page']['permissions']], '
	</aside>';
		}
	}

	echo '
	<section itemscope itemtype="http://schema.org/Article">
		<div id="display_head" class="windowbg">
			<h2 class="display_title" itemprop="headline">
				<span id="top_subject">', $context['page_title'];

	if ($context['lp_page']['can_edit']) {
		echo '
					<a class="floatright" href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $context['lp_page']['id'], '" title="', $txt['edit'], '">
						<svg aria-hidden="true" width="30" height="30" focusable="false" data-prefix="fas" data-icon="edit" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z"></path></svg>
					</a>';
	}

	echo '
				</span>
			</h2>';

	if (!empty($context['lp_page']['options']['show_author_and_date'])) {
		echo '
			<p>
				<span class="floatleft"><i class="fas fa-user" aria-hidden="true"></i> <span itemprop="author">', $context['lp_page']['author'], '</span></span>
				<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
					<i class="fas fa-clock" aria-hidden="true"></i> ', $context['lp_page']['created'], !empty($context['lp_page']['updated_at']) ? ' / ' . $context['lp_page']['updated'] . ' <meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '">' : '', '
				</time>
			</p>';
	}

	echo '
			<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', $context['canonical_url'], '" content="', $context['canonical_url'], '">
			<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
				<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
					<img alt="" itemprop="url image" src="', $context['header_logo_url_html_safe'] ?: ($settings['images_url'] . '/thumbnail.png'), '" style="display:none">
				</span>
				<meta itemprop="name" content="', $context['forum_name_html_safe'], '">
				<meta itemprop="address" content="', !empty($modSettings['lp_page_itemprop_address']) ? $modSettings['lp_page_itemprop_address'] : $boardurl, '">
				<meta itemprop="telephone" content="', !empty($modSettings['lp_page_itemprop_phone']) ? $modSettings['lp_page_itemprop_phone'] : '', '">
			</span>
		</div>
		<article class="roundframe" itemprop="articleBody">
			<h3 style="display: none">', $context['lp_page']['author'], ' - ', $context['page_title'], '</h3>';

	if (!empty($context['lp_page']['tags']) && !empty($modSettings['lp_show_tags_on_page'])) {
		echo '
			<div class="smalltext">';

		foreach ($context['lp_page']['tags'] as $tag) {
			echo '
				<a class="button" href="', $tag['href'], '">', $tag['name'], '</a>';
		}

		echo '
			</div>
			<hr>';
	}

	if (!empty($settings['og_image']))
		echo '
			<meta itemprop="image" content="', $settings['og_image'], '">';

	echo '
			<div class="page_', $context['lp_page']['type'], '">', $context['lp_page']['content'], '</div>';

	// Extend with addons
	if (!empty($context['lp_page']['addons']))
		echo $context['lp_page']['addons'];

	echo '
		</article>';

	show_related_pages();

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
	global $modSettings, $context, $options, $txt;

	if (empty($modSettings['lp_show_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
		return;

	if ($modSettings['lp_show_comment_block'] == 'none')
		return;

	if (!empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'])) {
		echo $context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'];
		return;
	}

	if ($modSettings['lp_show_comment_block'] !== 'default')
		return;

	echo '
		<aside class="comments"', empty($context['user']['is_guest']) ? ' x-data' : '', '>
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="page_comments_toggle" class="fa toggle_', empty($options['collapse_header_page_comments']) ? 'up' : 'down', ' floatright fa-lg" style="display: none"></span>';

	if (empty($context['user']['is_guest'])) {
		echo '
					<a id="page_comments_link">', $txt['lp_comments'], '</a>';
	} else {
		echo $txt['lp_comments'];
	}

	echo '
				</h3>
			</div>
			<div
				id="page_comments"', empty($options['collapse_header_page_comments']) ? '' : '
				style="display: none"', empty($context['user']['is_guest']) ? '
				x-ref="page_comments"' : '', '
			>';

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

	if ($context['user']['is_logged']) {
		echo '
				<form
					id="comment_form"
					class="roundframe descbox"
					accept-charset="', $context['character_set'], '"
					x-ref="comment_form"
					@submit.prevent="comment.add($event.target, $refs)"
				>';

		show_toolbar();

		echo '
					<textarea
						id="message"
						name="message"
						class="content"
						placeholder="', $txt['lp_comment_placeholder'], '"
						maxlength="65534"
						@keyup="$refs.comment.disabled = !$event.target.value"
						@focus="comment.focus($event.target, $refs)"
						x-ref="message"
						tabindex="1"
						required
					></textarea>
					<input type="hidden" name="parent_id" value="0">
					<input type="hidden" name="counter" value="0">
					<input type="hidden" name="level" value="1">
					<input type="hidden" name="page_id" value="', $context['lp_page']['id'], '">
					<input type="hidden" name="page_title" value="', $context['page_title'], '">
					<input type="hidden" name="page_url" value="', $context['lp_current_page_url'], '">
					<input type="hidden" name="start" value="', $context['page_info']['start'], '">
					<input type="hidden" name="commentator" value="0">
					<button
						type="submit"
						class="button"
						name="comment"', empty($context['user']['is_guest']) ? '
						x-ref="comment"' : '', '
						disabled
					>', $txt['post'], '</button>
				</form>';
	}

	echo '
			</div>
		</aside>';

	if ($context['user']['is_logged'])
		echo '
		<script>
			new Toggler({
				isCurrentlyCollapsed: ', empty($options['collapse_header_page_comments']) ? 'false' : 'true', ',
				toggleAltExpandedTitle: ', JavaScriptEscape($txt['hide']), ',
				toggleAltCollapsedTitle: ', JavaScriptEscape($txt['show']), ',
				toggleMsgBlockTitle: ', JavaScriptEscape($txt['lp_comments']), ',
				useThemeSettings: true,
				useCookie: false
			});
		</script>';
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
function show_single_comment(array $comment, int $i = 0, int $level = 1)
{
	global $context, $txt;

	echo '
	<li
		id="comment', $comment['id'], '"
		class="col-xs-12 generic_list_wrapper bg ', $i % 2 == 0 ? 'even' : 'odd', '"
		data-id="', $comment['id'], '"
		data-counter="', $i, '"
		data-level="', $level, '"
		data-start="', $context['current_start'], '"
		data-commentator="', $comment['author_id'], '"
		itemprop="comment"
		itemscope="itemscope"
		itemtype="http://schema.org/Comment"', empty($context['user']['is_guest']) ? '
		x-ref="comment' . $comment['id'] . '"' : '', '
	>
		<div class="comment_avatar"', $context['right_to_left'] ? ' style="padding: 0 0 0 10px"' : '', '>
			', $comment['avatar'];

	if (!empty($context['lp_page']['author_id']) && $context['lp_page']['author_id'] == $comment['author_id'])
		echo '
			<span class="new_posts">', $txt['author'], '</span>';

	echo '
		</div>
		<div class="comment_wrapper"', $context['right_to_left'] ? ' style="padding: 0 55px 0 0"' : '', '>
			<div class="entry bg ', $i % 2 == 0 ? 'odd' : 'even', '">
				<div class="title">
					<span
						class="bg ', $i % 2 == 0 ? 'even' : 'odd', '"
						itemprop="creator"', $context['user']['is_logged'] ? ('
						style="cursor: pointer"
						data-parent="' . $comment['parent_id'] . '"') : '', empty($context['user']['is_guest']) ? '
						@click="comment.pasteNick($event.target, $refs)"' : '', '
					>
						', $comment['author_name'], '
					</span>
					<div class="comment_date bg ', $i % 2 == 0 ? 'even' : 'odd', '">
						<span itemprop="datePublished" content="' , $comment['created_at'], '">
							', $comment['created'], ' <a class="bbc_link" href="#comment', $comment['id'], '">#' , $comment['id'], '</a>
						</span>
					</div>
				</div>
				<div class="raw_content" style="display: none">', $comment['raw_message'], '</div>
				<div class="content" itemprop="text"', $context['user']['is_guest'] || $level >= 5 ? ' style="min-height: 3em"' : '', '>', $comment['message'], '</div>';

	if ($context['user']['is_logged']) {
		echo '
				<div class="smalltext">';

		if ($level < 5) {
			echo '
					<span class="reply_button" data-id="', $comment['id'], '" @click="comment.reply($event.target, $refs)"><i class="fas fa-reply"></i> ', $txt['reply'], '</span>';

			// Do not allow edit comments with children
			if (!empty($comment['children']))
				$comment['can_edit'] = false;

			// Only comment author can edit comments
			if ($comment['author_id'] == $context['user']['id'] && $comment['can_edit'])
				echo '
					<span class="modify_button" data-id="', $comment['id'], '" @click="comment.modify($event.target)"><i class="fas fa-edit"></i> ', $txt['modify'], '</span>
					<span class="update_button" data-id="', $comment['id'], '" @click="comment.update($event.target)"><i class="fas fa-save"></i> ', $txt['save'], '</span>
					<span class="cancel_button" data-id="', $comment['id'], '" @click="comment.cancel($event.target)"><i class="fas fa-undo"></i> ', $txt['modify_cancel'], '</span>';
		} else {
			echo '&nbsp;';
		}

		// Only comment author or admin can remove comments
		if ($comment['author_id'] == $context['user']['id'] || $context['user']['is_admin'])
			echo '
					<span class="remove_button floatright" data-id="', $comment['id'], '" @click="comment.remove($event.target)" @mouseover="$event.target.classList.toggle(\'error\')" @mouseout="$event.target.classList.toggle(\'error\')"><i class="fas fa-minus-circle"></i> ', $txt['remove'], '</span>';

		echo '
				</div>';
	}

	echo '
			</div>';

	if (!empty($comment['children'])) {
		echo '
			<ul class="comment_list row">';

		foreach ($comment['children'] as $child_comment)
			show_single_comment($child_comment, $i + 1, $level + 1);

		echo '
			</ul>';
	}

	echo '
		</div>
	</li>';
}

/**
 * Related pages template
 *
 * Шаблон похожих страниц
 *
 * @return void
 */
function show_related_pages()
{
	global $context, $txt;

	if (empty($context['lp_page']['related_pages']))
		return;

	echo '
		<div class="related_pages">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['lp_related_pages'], '</h3>
			</div>
			<div class="list">';

	foreach ($context['lp_page']['related_pages'] as $page) {
		echo '
				<div class="windowbg">';

		if (!empty($page['image'])) {
			echo '
					<a href="', $page['link'], '">
						<div class="article_image">
							<img alt="" src="', $page['image'], '">
						</div>
					</a>';
		}

		echo '
					<a href="', $page['link'], '">', $page['title'], '</a>
				</div>';
	}

	echo '
			</div>
		</div>';
}

/**
 * BBCode toolbar
 *
 * Панель вставки ББ-кода
 *
 * @return void
 */
function show_toolbar()
{
	global $context, $editortxt;

	if (empty($context['lp_allowed_bbc']))
		return;

	echo '
	<div class="toolbar descbox" x-ref="toolbar" @click="toolbar.pressButton($event.target, $refs.message)">';

	if (in_array('b', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['bold'], '"><i class="fas fa-bold"></i></span>';
	}

	if (in_array('i', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['italic'], '"><i class="fas fa-italic"></i></span>';
	}

	if (in_array('b', $context['lp_allowed_bbc']) || in_array('i', $context['lp_allowed_bbc'])) {
		echo '&nbsp;';
	}

	if (in_array('list', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['bullet_list'], '"><i class="fas fa-list-ul"></i></span>
		<span class="button" title="', $editortxt['numbered_list'], '"><i class="fas fa-list-ol"></i></span>
		&nbsp;';
	}

	if (in_array('youtube', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['insert_youtube_video'], '"><i class="fab fa-youtube"></i></span>';
	}

	if (in_array('img', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['insert_image'], '"><i class="fas fa-image"></i></span>';
	}

	if (in_array('url', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['insert_link'], '"><i class="fas fa-link"></i></span>';
	}

	if (in_array('youtube', $context['lp_allowed_bbc']) || in_array('img', $context['lp_allowed_bbc']) || in_array('url', $context['lp_allowed_bbc'])) {
		echo '&nbsp;';
	}

	if (in_array('code', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['code'], '"><i class="fas fa-code"></i></span>';
	}

	if (in_array('quote', $context['lp_allowed_bbc'])) {
		echo '
		<span class="button" title="', $editortxt['insert_quote'], '"><i class="fas fa-quote-right"></i></span>';
	}

	echo '
	</div>';
}
