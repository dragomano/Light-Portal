@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	@include('partials.pagination')

	<div class="article-container columns-{{ $context['lp_frontpage_num_columns'] }}">

		@set($articles = $context['lp_frontpage_articles'])
		@set($firstArticle = array_shift($articles))

		@unless (empty($firstArticle))
			<div class="featured-article article">
				@unless (empty($firstArticle['image']))
					<img
						class="lazy"
						data-src="{{ $firstArticle['image'] }}"
						alt="{{ $firstArticle['title'] }}"
					/>
				@endunless

				<div class="article-content">
					<div class="article-title">
						<a href="{{ $firstArticle['link'] }}">{{ $firstArticle['title'] }}</a>
					</div>

					@unless (empty($firstArticle['datetime']))
						<div class="article-date">
							<time datetime="{{ $firstArticle['datetime'] }}">
								@icon('date') {!! $firstArticle['date'] !!}
							</time>
						</div>
					@endunless
				</div>
			</div>
		@endunless

		@foreach ($articles as $article)
			<div class="article">
				@unless (empty($article['image']))
					<img
						class="lazy"
						data-src="{{ $article['image'] }}"
						alt="{{ $article['title'] }}"
					/>
				@endunless

				<div class="article-content">
					<div class="article-title">
						<a href="{{ $article['link'] }}">{{ $article['title'] }}</a>
					</div>

					@unless (empty($article['datetime']))
						<div class="article-date">
							<time datetime="{{ $article['datetime'] }}">
								@icon('date') {!! $article['date'] !!}
							</time>
						</div>
					@endunless
				</div>
			</div>
		@endforeach
	</div>

	@include('partials.pagination', ['position' => 'bottom'])

@empty ($context['lp_active_blocks'])
</div>
@endempty