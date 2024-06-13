<span class="card_date smalltext">
	@unless (empty($article['section']['name']))
		<a class="floatleft" href="{{ $article['section']['link'] }}">
			@empty ($article['section']['icon'])
				@icon('category')
			@else
				{!! $article['section']['icon'] !!}
			@endempty

			{{ $article['section']['name'] }}
		</a>
	@endunless

	@if ($article['is_new'] && empty($article['image']))
		<span class="new_posts">
			{{ $txt['new'] }}
		</span>
	@endif

	@unless (empty($article['datetime']))
		<time class="floatright" datetime="{{ $article['datetime'] }}">
			@icon('date') {!! $article['date'] !!}
		</time>
	@endunless
</span>