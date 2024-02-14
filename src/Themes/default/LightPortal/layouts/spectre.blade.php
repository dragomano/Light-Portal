@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	<div class="lp_frontpage_articles article_spectre_view">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
		<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
			<div class="card">

				@if (!empty($article['image']))
				<a class="card-image" href="{{ $article['link'] }}">
					<img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
				</a>
				@endif

				<div class="card-header">

					@if (!empty($modSettings['lp_show_views_and_comments']))
					<span class="floatright">
						@if (!empty($article['views']['num']))
							@icon(['views', $article['views']['title']])
							{{ $article['views']['num'] }}
						@endif

						@if (!empty($article['views']['after']))
							{!! $article['views']['after'] !!}
						@endif

						@if (!empty($article['is_redirect']))
							@icon('redirect')
						@endif

						@if (!empty($article['replies']['num']))
							@icon(['replies', $article['replies']['title']])
							{{ $article['replies']['num'] }}
						@endif

						@if (!empty($article['replies']['after']))
							{!! $article['replies']['after'] !!}
						@endif
					</span>
					@endif

					<a class="card-title" href="{{ $article['link'] }}">{{ $article['title'] }}</a>
					<div class="card-subtitle">{!! $article['date'] !!}</div>
				</div>

				@if (!empty($article['teaser']))
				<div class="card-body">
					{{ $article['teaser'] }}
				</div>
				@endif
			</div>
		</div>
		@endforeach

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty