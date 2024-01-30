<?php

use Bugo\LightPortal\Utils\{Config, Icon, Lang, Utils};

function show_user_info($data): void
{
	echo '
	<ul class="centertext">
		<li>', Lang::$txt['hello_member'], ' <strong>', $data['name_color'], '</strong></li>';

	if ($data['avatar']) {
		echo '
		<li>', $data['avatar']['image'], '</li>';
	}

	echo '
		<li>', $data['primary_group'] ?: ($data['post_group'] ?: ''), '</li>
		<li>', $data['group_icons'], '</li>';

	if (Utils::$context['user']['is_admin']) {
		echo '
		<li class="lefttext">
			<hr>
			', Icon::get('plus_circle'), ' <a href="', Config::$scripturl, '?action=admin;area=lp_blocks;sa=add;', Utils::$context['session_var'], '=', Utils::$context['session_id'], '">
				', Lang::$txt['lp_blocks_add'], '
			</a>
		</li>';
	}

	if (Utils::$context['allow_light_portal_manage_pages_own']) {
		echo '
		<li class="lefttext">
			<hr>
			', Icon::get('plus_circle'), ' <a href="', Config::$scripturl, '?action=admin;area=lp_pages;sa=add;', Utils::$context['session_var'], '=', Utils::$context['session_id'], '">
				', Lang::$txt['lp_pages_add'], '
			</a>
		</li>';
	}

	if (Utils::$context['allow_light_portal_manage_pages_any']) {
		echo '
		<li class="lefttext">
			<hr>
			', Icon::get('pager'), ' <a href="', Config::$scripturl, '?action=admin;area=lp_pages;sa=main;moderate;', Utils::$context['session_var'], '=', Utils::$context['session_id'], '">
				', Lang::$txt['lp_page_moderation'], '
			</a>
		</li>';
	}

	echo '
		<li>
			<hr>
			<span class="floatleft">
				', Icon::get('user'), ' <a href="', $data['href'], '">', Lang::$txt['profile'], '</a>
			</span>
			<span class="floatright">
				', Icon::get('sign_out_alt'), ' <a href="', Config::$scripturl, '?action=logout;', Utils::$context['session_var'], '=', Utils::$context['session_id'], '">', Lang::$txt['logout'], '</a>
			</span>
		</li>
	</ul>';
}

function show_user_info_for_guests(): void
{
	echo '
	<ul class="centertext">
		<li>', Lang::$txt['hello_member'], ' ', Lang::$txt['guest'], '</li>
		<li><img alt="*" src="', Config::$modSettings['avatar_url'], '/default.png" width="100" height="100"></li>
		<li>';

	if (Utils::$context['can_register']) {
		echo '
			<span class="floatleft">
				', Icon::get('user_plus'), ' <a href="', Config::$scripturl, '?action=signup">', Lang::$txt['register'], '</a>
			</span>';
	}

	echo '
			<span', Utils::$context['can_register'] ? ' class="floatright"' : '', '>
				', Icon::get('sign_in_alt'), ' <a href="', Config::$scripturl, '?action=login" onclick="return reqOverlayDiv(this.href, ', Utils::JavaScriptEscape(Lang::$txt['login']), ');">', Lang::$txt['login'], '</a>
			</span>
		</li>
	</ul>';
}
