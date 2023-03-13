<?php

function template_show_page()
{
	global $context, $modSettings, $txt, $scripturl, $settings;

	if (! empty($context['lp_page']['errors'])) {
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

	if ($context['lp_page']['can_edit']) {
		echo '
	<aside class="infobox">
		<strong>', $txt['edit_permissions'], '</strong>: ', $txt['lp_permissions'][$context['lp_page']['permissions']], '
		<a class="button floatright" href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $context['lp_page']['id'], '">', $context['lp_icon_set']['edit'], '<span class="hidden-xs">', $txt['edit'], '</span></a>';

		if (! (empty($context['user']['is_admin']) || empty($modSettings['lp_frontpage_mode']) || $modSettings['lp_frontpage_mode'] !== 'chosen_pages')) {
			echo '
		<a class="button floatright" href="', $context['canonical_url'], ';promote">', $context['lp_icon_set']['home'], '<span class="hidden-xs hidden-sm">', $txt['lp_' . (in_array($context['lp_page']['id'], $context['lp_frontpage_pages']) ? 'remove_from' : 'promote_to') . '_fp'], '</span></a>';
		}

		echo '
	</aside>';
	}

	echo '
	<section itemscope itemtype="https://schema.org/Article">
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', $context['canonical_url'], '" content="', $context['canonical_url'], '">';

	if (! isset($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_author_and_date'])) {
		echo '
		<div id="display_head" class="windowbg">';

		if (! isset($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_title'])) {
			echo '
			<h2 class="display_title" itemprop="headline">
				<span id="top_subject">', $context['page_title'], '</span>
			</h2>';
		}

		if (! empty($context['lp_page']['options']['show_author_and_date'])) {
			echo '
			<p>
				<span class="floatleft" itemprop="author" itemscope itemtype="https://schema.org/Person">
					', $context['lp_icon_set']['user'], '<span itemprop="name">', $context['lp_page']['author'], '</span>
					<meta itemprop="url" content="', $scripturl, '?action=profile;u=', $context['lp_page']['author_id'], '">
				</span>
				', $context['lp_page']['post_author'] ?? '', '
				<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
					', $context['lp_icon_set']['date'], $context['lp_page']['created'], empty($context['lp_page']['updated_at']) ? '' : (' / ' . $context['lp_page']['updated'] . ' <meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '">'), '
				</time>
			</p>';
		}

		echo '
		</div>';
	}

	echo '
		<article class="roundframe" itemprop="articleBody">
			<h3 style="display: none">', $context['lp_page']['author'], ' - ', $context['page_title'], '</h3>';

	if (! empty($context['lp_page']['tags']) && ! empty($modSettings['lp_show_tags_on_page'])) {
		echo '
			<div class="smalltext">';

		foreach ($context['lp_page']['tags'] as $tag) {
			echo '
				<a class="button" href="', $tag['href'], '">', $context['lp_icon_set']['tag'], $tag['name'], '</a>';
		}

		echo '
			</div>
			<hr>';
	}

	if (! empty($settings['og_image'])) {
		echo '
			<meta itemprop="image" content="', $settings['og_image'], '">';
	}

	echo '
			<div class="page_', $context['lp_page']['type'], '">', $context['lp_page']['content'], '</div>';

	// Extend with addons
	echo $context['lp_page']['post_content'] ?? '';

	echo '
		</article>';

	show_prev_next_links();

	show_related_pages();

	show_comment_block();

	echo '
	</section>';
}

function show_prev_next_links()
{
	global $context;

	if (empty($context['lp_page']['prev']) && empty($context['lp_page']['next']))
		return;

	echo '
	<div class="generic_list_wrapper">';

	if (!empty($context['lp_page']['prev']))
		echo '
		<a class="floatleft" href="', $context['lp_page']['prev']['link'], '">', $context['lp_icon_set']['arrow_left'], ' ', $context['lp_page']['prev']['title'], '</a>';

	if (!empty($context['lp_page']['next']))
		echo '
		<a class="floatright" href="', $context['lp_page']['next']['link'], '">', $context['lp_page']['next']['title'], ' ', $context['lp_icon_set']['arrow_right'], '</a>';

	echo '
	</div>';
}

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

		if (! empty($page['image'])) {
			echo '
					<a href="', $page['link'], '">
						<div class="article_image">
							<img alt="', $page['title'], '" src="', $page['image'], '">
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

function show_comment_block()
{
	global $modSettings, $context, $options, $txt;

	if (empty($modSettings['lp_show_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
		return;

	if ($modSettings['lp_show_comment_block'] === 'none')
		return;

	if (! empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'])) {
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
					<div class="pagesection">', $context['page_index'], '</div>
				</div>';

	$i = 0;
	if (! empty($context['lp_page']['comments'])) {
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
					<div class="pagesection">', $context['page_index'], '</div>
				</div>';

	if ($context['user']['is_logged']) {
		echo '
				<div class="comment_add roundframe descbox" x-ref="comment_form">';

		show_toolbar();

		echo '
					<textarea
						class="content"
						placeholder="', $txt['lp_comment_placeholder'], '"
						@keyup="$refs.comment.disabled = ! $event.target.value"
						@focus="comment.focus($event.target, $refs)"
						x-ref="message"
					></textarea>
					<button
						class="button"
						name="comment"
						data-page="', $context['lp_page']['id'], '"
						x-ref="comment"
						@click.self="comment.add($event.target, $refs)"
						disabled
					>', $context['lp_icon_set']['submit'], $txt['post'], '</button>
				</div>';
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

function show_single_comment(array $comment, int $i = 0, int $level = 1)
{
	global $context, $txt, $modSettings;

	if (empty($comment['poster']['id']))
		return;

	echo '
	<li
		class="col-xs-12 generic_list_wrapper bg ', $i % 2 == 0 ? 'even' : 'odd', '"
		id="comment', $comment['id'], '"
		data-id="', $comment['id'], '"
		data-counter="', $i, '"
		data-level="', $level, '"
		data-start="', $comment['start'] ?? $context['current_start'], '"
		data-commentator="', $comment['poster']['id'], '"
		itemprop="comment"
		itemscope="itemscope"
		itemtype="https://schema.org/Comment"', empty($context['user']['is_guest']) ? '
		x-ref="comment' . $comment['id'] . '"
		x-data="{ replyForm: false }"' : '', '
	>
		<div class="comment_avatar"', $context['right_to_left'] ? ' style="padding: 0 0 0 10px"' : '', '>
			', $comment['poster']['avatar'];

	if (! empty($context['lp_page']['author_id']) && $context['lp_page']['author_id'] == $comment['poster']['id'])
		echo '
			<span class="new_posts">', $txt['author'], '</span>';

	echo '
		</div>
		<div class="comment_wrapper"', $context['right_to_left'] ? ' style="padding: 0 55px 0 0"' : '', '>
			<div class="entry bg ', $i % 2 == 0 ? 'odd' : 'even', ' ', $comment['rating_class'] ?? '', '">
				<div class="title">
					<span
						class="bg ', $i % 2 == 0 ? 'even' : 'odd', '"
						itemprop="creator"', $context['user']['is_logged'] && $level < 5 ? ('
						style="cursor: pointer"
						data-id="' . $comment['id'] . '"') : '', $context['user']['is_logged'] && $level < 5 ? '
						@click="comment.pasteNick($event.target, $refs)"' : '', '
					>
						', $comment['poster']['name'], '
					</span>';

	// Authors cannot vote their own comments
	if (! empty($modSettings['lp_allow_comment_ratings'])) {
		echo '
					<div class="rating_area bg ', $i % 2 == 0 ? 'even' : 'odd', '"', $comment['can_rate'] ? ' @click="comment.like($event.target)"' : '', ' x-show="', $comment['poster']['id'] === $context['user']['id'] ? 'false' : 'true', '">';

		if (empty($comment['is_rated'])) {
			if ($comment['can_rate'])
				echo '
						<span class="like_button floatright" @mouseover="$event.target.classList.toggle(\'error\')" @mouseout="$event.target.classList.toggle(\'error\')">
							', str_replace(' class="', ' data-id="' . $comment['id'] . '" data-action="dislike" title="' . $txt['lp_dislike_button'] . '" class="', $context['lp_icon_set']['dislike']), '
						</span>';

			if ($comment['poster']['id'] !== $context['user']['id'])
				show_rating($comment);

			if ($comment['can_rate'])
				echo '
						<span class="like_button floatright" @mouseover="$event.target.classList.toggle(\'success\')" @mouseout="$event.target.classList.toggle(\'success\')">
							', str_replace(' class="', ' data-id="' . $comment['id'] . '" data-action="like" title="' . $txt['lp_like_button'] . '" class="', $context['lp_icon_set']['like']), '
						</span>';
		} else {
			show_rating($comment);

			if ($comment['can_rate'])
				echo '
						<span class="like_button floatright">
							', str_replace(' class="', ' data-id="' . $comment['id'] . '" data-action="unlike" title="' . $txt['poll_change_vote'] . '" class="', $context['lp_icon_set']['unlike']), '
						</span>';
		}

		echo '
					</div>';
	}

	echo '
					<div class="comment_date bg ', $i % 2 == 0 ? 'even' : 'odd', '">
						<span itemprop="datePublished" content="' , $comment['created_at'], '">
							', $comment['created'], ' <a class="bbc_link" href="#comment', $comment['id'], '">#' , $comment['id'], '</a>
						</span>
					</div>
				</div>
				<div class="raw_content" style="display: none">', $comment['raw_message'], '</div>
				<div class="content" itemprop="text">', $comment['message'], '</div>';

	if ($context['user']['is_logged']) {
		echo '
				<div class="smalltext">
					&nbsp;';

		if ($level < 5) {
			echo '
					<span class="reply_button" data-id="', $comment['id'], '" @click.self="replyForm = true; $nextTick(() => $refs.reply_message.focus())">', $context['lp_icon_set']['reply'], $txt['reply'], '</span>';
		}

		// Only comment author can edit comments
		if ($comment['can_edit'] && empty($comment['children']) && $comment['poster']['id'] === $context['user']['id']) {
			echo '
					<span class="modify_button" data-id="', $comment['id'], '" @click.self="comment.modify($event.target)">', $context['lp_icon_set']['edit'], $txt['modify'], '</span>
					<span class="update_button" data-id="', $comment['id'], '" @click.self="comment.update($event.target)">', $context['lp_icon_set']['save'], $txt['save'], '</span>
					<span class="cancel_button" data-id="', $comment['id'], '" @click.self="comment.cancel($event.target)">', $context['lp_icon_set']['undo'], $txt['modify_cancel'], '</span>';
		}

		// Only comment author or admin can remove comments
		if ($comment['poster']['id'] === $context['user']['id'] || $context['user']['is_admin']) {
			echo '
					<span class="remove_button floatright" data-id="', $comment['id'], '" data-level="', $level, '" @click.once="comment.remove($event.target)" @mouseover="$event.target.classList.toggle(\'error\')" @mouseout="$event.target.classList.toggle(\'error\')">', $context['lp_icon_set']['remove'], $txt['remove'], '</span>';
		}

		echo '
				</div>';
	}

	echo '
			</div>';

	if ($context['user']['is_logged'] && $level < 5) {
		echo '
			<div
				class="comment_reply roundframe descbox"
				x-ref="reply_comment_form"
				x-show="replyForm"
			>';

		show_toolbar();

		echo '
				<textarea
					class="content"
					placeholder="', $txt['lp_comment_placeholder'], '"
					@keyup="$refs.reply_comment.disabled = ! $event.target.value"
					x-ref="reply_message"
				></textarea>
				<button class="button active" @click.self="replyForm = false; $refs.reply_message.value = \'\'">', $txt['modify_cancel'], '</button>
				<button
					class="button"
					name="comment"
					data-id="', $comment['id'], '"
					data-page="', $context['lp_page']['id'], '"
					x-ref="reply_comment"
					@click.self="comment.addReply($event.target, $refs)"
					disabled
				>', $context['lp_icon_set']['submit'], $txt['post'], '</button>
			</div>';
	}

	if (! empty($comment['children'])) {
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

function show_rating(array $comment)
{
	echo '
	<span class="rating_span', $comment['rating'] < 0 ? ' error' : ($comment['rating'] > 0 ? ' success' : ''), '">', $comment['rating'], '</span>';
}

function show_toolbar()
{
	global $context, $editortxt;

	if (empty($context['lp_allowed_bbc']))
		return;

	echo '
	<div class="toolbar descbox" x-ref="toolbar" @click="toolbar.pressButton($event.target)">';

	if (in_array('b', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="bold" title="' . $editortxt['bold'] . '" class="button ', $context['lp_icon_set']['bold']);

	if (in_array('i', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="italic" title="' . $editortxt['italic'] . '" class="button ', $context['lp_icon_set']['italic']);

	if (in_array('b', $context['lp_allowed_bbc']) || in_array('i', $context['lp_allowed_bbc']))
		echo '&nbsp;';

	if (in_array('youtube', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="youtube" title="' . $editortxt['insert_youtube_video'] . '" class="button ', $context['lp_icon_set']['youtube']);

	if (in_array('img', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="image" title="' . $editortxt['insert_image'] . '" class="button ', $context['lp_icon_set']['image']);

	if (in_array('url', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="link" title="' . $editortxt['insert_link'] . '" class="button ', $context['lp_icon_set']['link']);

	if (in_array('youtube', $context['lp_allowed_bbc']) || in_array('img', $context['lp_allowed_bbc']) || in_array('url', $context['lp_allowed_bbc']))
		echo '&nbsp;';

	if (in_array('code', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="code" title="' . $editortxt['code'] . '" class="button ', $context['lp_icon_set']['code']);

	if (in_array('quote', $context['lp_allowed_bbc']))
		echo str_replace(' class="', ' data-type="quote" title="' . $editortxt['insert_quote'] . '" class="button ', $context['lp_icon_set']['quote']);

	echo '
	</div>';
}
