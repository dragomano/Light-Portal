@unless (empty($context['lp_page']['prev']) && empty($context['lp_page']['next']))
	<div class="generic_list_wrapper">
		@unless (empty($context['lp_page']['prev']))
			<a class="floatleft" href="{{ $context['lp_page']['prev']['link'] }}">
				@icon('arrow_left') {{ $context['lp_page']['prev']['title'] }}
			</a>
		@endunless

		@unless (empty($context['lp_page']['next']))
			<a class="floatright" href="{{ $context['lp_page']['next']['link'] }}">
				{{ $context['lp_page']['next']['title'] }} @icon('arrow_right')
			</a>
		@endunless
	</div>
@endunless
