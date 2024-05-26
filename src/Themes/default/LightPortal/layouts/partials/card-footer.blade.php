<div>
	@unless (empty($article['category']))
		<span class="card_author">
			@icon('category'){{ $article['category'] }}
		</span>
	@endunless

	@if (!empty($modSettings['lp_show_author']) && !empty($article['author']))
		@if (!empty($article['author']['id']) && !empty($article['author']['name']))
			<a href="{{ $article['author']['link'] }}" class="card_author">
				@icon('user'){{ $article['author']['name'] }}
			</a>
		@else
			<span class="card_author">{{ $txt['guest_title'] }}</span>
		@endif
	@endif

	@unless (empty($modSettings['lp_show_views_and_comments']))
		<span class="floatright">
			@unless (empty($article['views']['num']))
				@icon(['views', $article['views']['title']])
				{{ $article['views']['num'] }}
			@endunless

			@unless (empty($article['views']['after']))
				{!! $article['views']['after'] !!}
			@endunless

			@unless (empty($article['is_redirect']))
				@icon('redirect')
			@endunless

			@unless (empty($article['replies']['num']))
				@icon(['replies', $article['replies']['title']])
				{{ $article['replies']['num'] }}
			@endunless

			@unless (empty($article['replies']['after']))
				{!! $article['replies']['after'] !!}
			@endunless
		</span>
	@endunless
</div>