@php use Bugo\Compat\Utils; @endphp
@php $avatarUrl = "{$modSettings['avatar_url']}/default.png" @endphp

<ul class="centertext">
	<li>{{ $txt['hello_member'] }} {{ $txt['guest'] }}</li>
	<li>
		<img
			alt="*"
			src="{{ $avatarUrl }}"
			width="100"
			height="100"
		>
	</li>
	<li style="display: flex; gap: 20px">
		@unless (empty($context['can_register']))
			<span>
                @icon('user_plus', $txt['register'] ?? '')
                <a href="{{ $scripturl }}?action=signup">{{ $txt['register'] }}</a>
            </span>
		@endunless

		<span @class(['centertext' => ! empty($context['can_register'])])>
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
