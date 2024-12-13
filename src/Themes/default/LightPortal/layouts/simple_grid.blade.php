@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	@include('partials.pagination')

	<div class="simple-grid columns-{{ $context['lp_frontpage_num_columns'] }}">
		@foreach ($context['lp_frontpage_articles'] as $article)
			<div class="item">
				@unless (empty($article['image']))
					<img class="lazy" data-src="{{ $article['image'] }}" alt="{{ $article['title'] }}" />
				@endunless

				<div>
					<a href="{{ $article['link'] }}">{{ $article['title'] }}</a>
				</div>
			</div>
		@endforeach
	</div>

	@include('partials.pagination', ['position' => 'bottom'])

@empty ($context['lp_active_blocks'])
</div>
@endempty