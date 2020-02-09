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
		<div id="display_head" class="roundframe">
			<h2 class="display_title" itemprop="headline">
				<span id="top_subject">', $context['page_title'], $context['lp_page']['can_edit'] ? '<a class="floatright" href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '</span>
			</h2>';

	if (!empty($context['lp_page']['options']['show_author_and_date'])) {
		echo '
			<p>
				<span class="floatleft"><i class="fas fa-user" aria-hidden="true"></i> <span itemprop="author">', $context['lp_page']['author'], '</span></span>
				<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
					<i class="fas fa-clock" aria-hidden="true"></i> ', $context['lp_page']['created'], !empty($context['lp_page']['updated_at']) ? ' (<meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '">' . (isset($txt['modified_time']) ? $txt['modified_time'] . ': ' : '') . $context['lp_page']['updated'] . ')' : '', '
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
		<article class="windowbg" itemprop="articleBody">
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
	global $context, $modSettings, $txt, $options;

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
		<div class="cat_bar"', empty($options['collapse_header_page_comments']) ? '' : ' style="border-radius: 6px;"', '>
			<h3 class="catbg">
				<span id="page_comments_toggle" class="fa toggle_down floatright fa-lg" style="display: none;"></span>
				<a id="page_comments_link">', $txt['lp_comments'], '</a>
			</h3>
		</div>
		<div id="page_comments" class="roundframe noup"', empty($options['collapse_header_page_comments']) ? '' : ' style="display: none;"', '>';

	if ($context['page_info']['num_pages'] > 1)
		echo '
			<div class="centertext">', $context['page_index'], '</div>';

	$i = 0;
	if (!empty($context['lp_page']['comments'])) {
		foreach ($context['lp_page']['comments'] as $comment)
			show_single_comment($comment, $i++);
	}

	if ($context['page_info']['num_pages'] > 1)
		echo '
			<div class="centertext">
				<div class="pagesection">
					<div class="pagelinks">', $context['page_index'], '</div>
				</div>
			</div>';

	if (!$context['user']['is_guest'])
		echo '
			<form id="comment_form" class="descbox" action="', $context['canonical_url'], $context['lp_page']['alias'] == '/' ? '?' : ';', 'new_comment" method="post" accept-charset="', $context['character_set'], '">
				<textarea id="message" name="message" class="content" cols="20" rows="5" placeholder="', $txt['lp_comment_placeholder'], '" required></textarea>
				<input type="hidden" name="parent_id" value="0">
				<input type="hidden" name="i" value="0">
				<input type="hidden" name="page_id" value="', $context['lp_page']['id'], '">
				<input type="hidden" name="start" value="', (int) $_REQUEST['start'], '">
				<button type="submit" class="button" name="comment" disabled>', $txt['post'], '</button>
			</form>';

	echo '
		</div>
	</aside>
	<script>';

	if (!$context['user']['is_guest'])
		echo '
		jQuery(document).ready(function($) {
			$("fieldset span.reply_button").on("click", function() {
				let parent_id = $(this).parents("fieldset").attr("data-id"),
					i = $(this).parents("fieldset").attr("data-i");
				$("#comment_form").children("input[name=parent_id]").val(parent_id);
				$("#comment_form").children("input[name=i]").val(i);
				$("textarea.content").focus();
			});
			let work = "', $context['canonical_url'], ';del_comment";
			$("fieldset span.remove_button").on("click", function() {
				if (!confirm("', $txt['quickmod_confirm'], '"))
					return false;
				let item = $(this).parents("fieldset").attr("data-id");
				if (item) {
					$.post(work, {del_comment: item});
					$(this).closest("fieldset").slideUp();
				}
			});
			$("#comment_form").on("keyup", function (e) {
				if ($(e.target).attr("name")) {
					if ($("#message").val() != "") {
						$("button[name=comment]").prop("disabled", false);
					} else {
						$("button[name=comment]").prop("disabled", true);
					}
				}
			});
			$("#comment_form").on("submit", (function (e) {
				$.ajax({
					type: $(this).attr("method"),
					url: $(this).attr("action"),
					data: $(this).serialize(),
					dataType: "json",
					success: function (data) {
						$("#comment_form")[0].reset();
						let comment = data.comment;
						if (data.parent != 0) {
							$("fieldset[data-id=" + data.parent + "]").append(comment).slideDown();
						} else {
							$(comment).insertBefore("#comment_form").slideDown();
						}
						window.location.href = "', $context['canonical_url'], $context['lp_page']['alias'] == '/' ? '?' : ';', 'start=" + data.start + "#comment" + data.item;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(errorThrown);
						alert(', JavaScriptEscape($txt['error_occured']), ');
					}
				});
				e.preventDefault();
			}));
		});';

	echo '
		let oPageCommentsToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_header_page_comments']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'page_comments\'
			],
			aSwapImages: [
				{
					sId: \'page_comments_toggle\',
					altExpanded: ', JavaScriptEscape($txt['hide']), ',
					altCollapsed: ', JavaScriptEscape($txt['show']), '
				}
			],
			aSwapLinks: [
				{
					sId: \'page_comments_link\',
					msgExpanded: ', JavaScriptEscape($txt['lp_comments']), ',
					msgCollapsed: ', JavaScriptEscape($txt['lp_comments']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_header_page_comments\',
				sSessionId: smf_session_id,
				sSessionVar: smf_session_var,
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'upshrinkPC\'
			}
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
 * @return void
 */
function show_single_comment($comment, $i = 0)
{
	global $context, $txt;

	echo '
	<fieldset id="comment', $comment['id'], '" class="generic_list_wrapper bg ', $i % 2 == 0 ? 'even' : 'odd', '" data-id="', $comment['id'], '" data-i="', $i, '" itemprop="comment" itemscope="itemscope" itemtype="http://schema.org/Comment">
		<legend>
			<span class="generic_list_wrapper">
				<span itemprop="creator">', $comment['author_name'], '</span>,
				<span class="comment_date" itemprop="datePublished" content="' , $comment['created_at'], '"><a href="#comment', $comment['id'], '">' , $comment['created'], '</a></span>
			</span>
		</legend>
		<div class="content" itemprop="text">', $comment['message'], '</div>';

	if ((!$context['user']['is_guest'] && $comment['author_id'] == $context['user']['id']) || $context['user']['is_admin']) {
		echo '
		<div class="floatright">
			<span class="button reply_button"> ', $txt['reply'], '</span>
			<span class="button remove_button"> ', $txt['remove'], '</span>
		</div>';
	}

	if (!empty($comment['childs'])) {
		foreach ($comment['childs'] as $children_comment)
			show_single_comment($children_comment, $i + 1);
	}

	echo '
	</fieldset>';
}
