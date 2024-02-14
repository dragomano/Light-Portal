@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	<div class="lp_frontpage_articles article_simple_view">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
		<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
			@if (!empty($article['image']))
			<div class="article_image lazy" data-bg="{{ $article['image'] }}"></div>
			@endif

			<div class="mt-6 body">
				<a class="article_title" href="{{ $article['link'] }}">{{ $article['title'] }}</a>

				@if (!empty($article['teaser']))
				<p class="article_teaser">{{ $article['teaser'] }}</p>
				@endif
			</div>

			<div class="mt-6">
				<a class="bbc_link" href="{{ $article['link'] }}">{{ $txt['lp_read_more'] }}</a>
			</div>
		</div>
		@endforeach

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty