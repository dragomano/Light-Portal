<?php

function show_user_info($data)
{
	global $txt, $context, $scripturl;

	echo '
	<ul class="centertext">
		<li>', $txt['hello_member'], ' <strong>', $data['name_color'], '</strong></li>';

	if ($data['avatar']) {
		echo '
		<li>', $data['avatar']['image'], '</li>';
	}

	echo '
		<li>', $data['primary_group'] ?: ($data['post_group'] ?: ''), '</li>
		<li>', $data['group_icons'], '</li>';

	if ($context['user']['is_admin']) {
		echo '
		<li class="lefttext">
			<hr>
			', $context['lp_icon_set']['plus_circle'], ' <a href="', $scripturl, '?action=admin;area=lp_blocks;sa=add;', $context['session_var'], '=', $context['session_id'], '">
				', $txt['lp_blocks_add'], '
			</a>
		</li>';
	}

	if ($context['allow_light_portal_manage_pages_own']) {
		echo '
		<li class="lefttext">
			<hr>
			', $context['lp_icon_set']['plus_circle'], ' <a href="', $scripturl, '?action=admin;area=lp_pages;sa=add;', $context['session_var'], '=', $context['session_id'], '">
				', $txt['lp_pages_add'], '
			</a>
		</li>';
	}

	if ($context['allow_light_portal_manage_pages_any']) {
		echo '
		<li class="lefttext">
			<hr>
			', $context['lp_icon_set']['pager'], ' <a href="', $scripturl, '?action=admin;area=lp_pages;sa=main;moderate;', $context['session_var'], '=', $context['session_id'], '">
				', $txt['lp_page_moderation'], '
			</a>
		</li>';
	}

	echo '
		<li>
			<hr>
			<span class="floatleft">
				', $context['lp_icon_set']['user'], ' <a href="', $data['href'], '">', $txt['profile'], '</a>
			</span>
			<span class="floatright">
				', $context['lp_icon_set']['sign_out_alt'], ' <a href="', $scripturl, '?action=logout;', $context['session_var'], '=', $context['session_id'], '">', $txt['logout'], '</a>
			</span>
		</li>
	</ul>';
}

function show_user_info_for_guests()
{
	global $txt, $modSettings, $context, $scripturl;

	echo '
	<ul class="centertext">
		<li>', $txt['hello_member'], ' ', $txt['guest'], '</li>
		<li><img alt="*" src="', $modSettings['avatar_url'], '/default.png" width="100" height="100"></li>
		<li>';

	if ($context['can_register']) {
		echo '
			<span class="floatleft">
				', $context['lp_icon_set']['user_plus'], ' <a href="', $scripturl, '?action=signup">', $txt['register'], '</a>
			</span>';
	}

	echo '
			<span', $context['can_register'] ? ' class="floatright"' : '', '>
				', $context['lp_icon_set']['sign_in_alt'], ' <a href="', $scripturl, '?action=login" onclick="return reqOverlayDiv(this.href, ', JavaScriptEscape($txt['login']), ');">', $txt['login'], '</a>
			</span>
		</li>
	</ul>';
}
