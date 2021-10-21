<?php

/**
 * Likes block template
 *
 * Шаблон блока лайков
 *
 * @param array $page
 * @return void
 */
function show_likes_block($page)
{
	global $modSettings, $scripturl, $context, $txt;

	if (empty($modSettings['enable_likes']) || (empty($page['likes']['can_like']) && empty($page['likes']['count'])))
		return;

	echo '
		<hr>
		<ul class="floatleft" style="list-style: none">';

	if (!empty($page['likes']['can_like'])) {
		echo '
			<li>
				<a href="', $scripturl, '?action=likes;ltype=lpp;sa=like;like=', $page['id'], ';', $context['session_var'], '=', $context['session_id'], '" class="msg_like"><span class="main_icons ', $page['likes']['you'] ? 'unlike' : 'like', '"></span> ', $page['likes']['you'] ? $txt['unlike'] : $txt['like'], '</a>
			</li>';
	}

	if (!empty($page['likes']['count'])) {
		$count = $page['likes']['count'];
		$base  = 'likes_';

		if ($page['likes']['you']) {
			$base = 'you_' . $base;
			$count--;
		}

		$base .= (isset($txt[$base . $count])) ? $count : 'n';

		echo '
			<li class="like_count smalltext">
				', sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=lpp;like=' . $page['id'] . ';' . $context['session_var'] . '=' . $context['session_id'], comma_format($count)), '
			</li>';
	}

	echo '
		</ul>';
}
