@extends('partials.base')

@section('content')
	<div class="lp_frontpage_articles article_spectre_view">
		@include('partials.pagination')

		@foreach ($context['lp_frontpage_articles'] as $article)
			<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
				<div class="card">

					@unless (empty($article['image']))
						<a class="card-image" href="{{ $article['link'] }}">
							<img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
						</a>
					@endunless

					<div class="card-header">

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

						<a class="card-title" href="{{ $article['link'] }}">{{ $article['title'] }}</a>
						<div class="card-subtitle">{!! $article['date'] !!}</div>
					</div>

					@unless (empty($article['teaser']))
						<div class="card-body">
							{{ $article['teaser'] }}
						</div>
					@endunless
				</div>
			</div>
		@endforeach

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
