@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	<div class="lp_frontpage_articles article_simple2_view">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
		<div class="col-xs-12">
			<div class="card">
				<div class="card-header">
					<div class="card-image lazy" data-bg="{{ $article['image'] }}"></div>
					<div class="card-title">
						<h3>{{ $article['title'] }}</h3>

						@if (!empty($article['datetime']))
						<time datetime="{{ $article['datetime'] }}">
							{!! $article['date'] !!}
						</time>
						@endif
					</div>
					<svg viewBox="0 0 100 100" preserveAspectRatio="none">
						<polygon points="50,0 100,0 50,100 0,100" />
					</svg>
				</div>
				<div class="card-body">
					<div class="card-body-inner">
						@if (!empty($article['datetime']))
						<time datetime="{{ $article['datetime'] }}">
							{!! $article['date'] !!}
						</time>
						@endif

						<h3>{{ $article['title'] }}</h3>

						@if (!empty($article['teaser']))
						<p class="article_teaser">
							{{ $article['teaser'] }}
						</p>
						@endif

						<a class="read_more" href="{{ $article['link'] }}">
							<span>{{ $txt['lp_read_more'] }}</span>
							<span class="arrow">&#x279c;</span>
						</a>
					</div>
				</div>
			</div>
		</div>
		@endforeach

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty