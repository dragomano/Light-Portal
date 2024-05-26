@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	@include('partials.pagination')

	<div class="lp_frontpage_articles article_simple3_view">
		@foreach ($context['lp_frontpage_articles'] as $article)
			<div>
				@unless (empty($article['image']))
					<img class="lazy" data-src="{{ $article['image'] }}" width="311" height="155" alt="{{ $article['title'] }}">
				@endunless

				<div class="title">
					<div><a class="bbc_link" href="{{ $article['link'] }}">{{ $article['title'] }}</a></div>

					@unless (empty($article['teaser']))
						<p>{{ $article['teaser'] }}</p>
					@endunless
				</div>

				@unless (empty($article['tags']))
					<details class="tags">
						<summary>{{ $txt['lp_tags'] }}</summary>

						@foreach ($article['tags'] as $tag)
							<a href="{{ $tag['href'] }}">#{{ $tag['title'] }}</a>
						@endforeach
					</details>
				@endunless
			</div>
		@endforeach
	</div>

	@include('partials.pagination', ['position' => 'bottom'])

@empty ($context['lp_active_blocks'])
</div>
@endempty