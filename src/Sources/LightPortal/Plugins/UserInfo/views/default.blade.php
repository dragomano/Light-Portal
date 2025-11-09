@php use Bugo\Compat\User; @endphp

<ul class="centertext">
	<li>
		{{ $txt['hello_member'] }}
		<strong>{!! $user['name_color'] !!}</strong>
	</li>

	@unless (empty($user['avatar']))
		<li><a href="{{ $user['href'] }}">{!! $user['avatar']['image'] !!}</a></li>
	@endunless

	<li>{{ $user['primary_group'] ?: ($user['post_group'] ?: '') }}</li>
	<li>{!! $user['group_icons'] !!}</li>

	@unless (empty($context['user']['is_admin']))
		<li class="lefttext">
			<hr>
			@icon('plus_circle', $txt['lp_blocks_add'] ?? '')
			<a href="{{ $scripturl }}?action=admin;area=lp_blocks;sa=add;{{ $context['session_var'] }}={{ $context['session_id'] }}">
				{{ $txt['lp_blocks_add'] }}
			</a>
		</li>
	@endunless

	@can ('light_portal_manage_pages_own')
		<li class="lefttext">
			<hr>
			@icon('plus_circle', $txt['lp_pages_add'] ?? '')
			<a href="{{ $scripturl }}?action=admin;area=lp_pages;sa=add;{{ $context['session_var'] }}={{ $context['session_id'] }}">
				{{ $txt['lp_pages_add'] }}
			</a>
		</li>
	@endcan

	@can ('light_portal_manage_pages_any')
		<li class="lefttext">
			<hr>
			@icon('pager', $txt['lp_user_info']['moderate_pages'] ?? '')
			<a href="{{ $scripturl }}?action=admin;area=lp_pages;sa=main;moderate;{{ $context['session_var'] }}={{ $context['session_id'] }}">
				{{ $txt['lp_user_info']['moderate_pages'] }}
			</a>
		</li>
	@endcan

	<li class="lefttext">
		<hr>
		@icon('sign_out_alt', $txt['logout'] ?? '')
		<a href="{{ $scripturl }}?action=logout;{{ $context['session_var'] }}={{ $context['session_id'] }}">
			{{ $txt['logout'] }}
		</a>
	</li>
</ul>
