
@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	@set($i = 0)
	<div class="lp_frontpage_articles article_alt3_view">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
			@empty ($i)
			<div class="roundframe article">
				<div class="card">
					@if (!empty($article['image']))
					<img
						class="lazy"
						data-src="{{ $article['image'] }}"
						alt="{{ $article['title'] }}"
					>
					@endif

					<div class="info">
						@if (!empty($article['datetime']))
						<time datetime="{{ $article['datetime'] }}">
							{!! $article['date'] !!}
						</time>
						@endif

						<h3><a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a></h3>

						@if (!empty($article['teaser']))
						<p>{{ $article['teaser'] }}</p>
						@endif
					</div>
				</div>
			</div>
			@endempty

			@set($i)
		@endforeach
	</div>

	@set($i = 0)
	@set($numItems = $modSettings['lp_num_items_per_page'] ?? 10)
	<div class="lp_frontpage_articles article_alt3_view">
		@foreach ($context['lp_frontpage_articles'] as $article)
			@set($i)

			@if ($i > 1)
			<div class="col-xs-12 col-sm-6 col-md-{{ $i > $numItems - 2 ? $context['lp_frontpage_num_columns'] + 2 : $context['lp_frontpage_num_columns'] }}">
				<div class="roundframe article">
					<div class="card">
						@if (!empty($article['image']))
						<img
							class="lazy"
							data-src="{{ $article['image'] }}"
							alt="{{ $article['title'] }}"
						>
						@endif

						<div class="info">
							@if (!empty($article['datetime']))
							<time datetime="{{ $article['datetime'] }}">
								{!! $article['date'] !!}
							</time>
							@endif

							<h3><a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a></h3>

							@if (!empty($article['teaser']))
							<p>{{ $article['teaser'] }}</p>
							@endif
						</div>
					</div>
				</div>
			</div>
			@endif
		@endforeach

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty