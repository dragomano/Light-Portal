@php use Bugo\Compat\Utils; @endphp

<ul class="centertext">
	<li>{{ $txt['hello_member'] }} {{ $txt['guest'] }}</li>
	<li>
		<img
			alt="*"
			src="{{ $modSettings['avatar_url'] }}/default.png"
			width="100"
			height="100"
		>
	</li>
	<li style="display: flex">
		@unless (empty($context['can_register']))
			<span class="floatleft">
                @icon('user_plus', $txt['register'] ?? '')
                <a href="{{ $scripturl }}?action=signup">{{ $txt['register'] }}</a>
            </span>
		@endunless

		<span @class(['floatright' => ! empty($context['can_register'])])>
            @icon('sign_in_alt', $txt['login'] ?? '')
            <a
				href="{{ $scripturl }}?action=login"
			   	onclick="return reqOverlayDiv(this.href, {{ Utils::escapeJavaScript($txt['login']) }});"
			>
                {{ $txt['login'] }}
            </a>
        </span>
	</li>
</ul>
