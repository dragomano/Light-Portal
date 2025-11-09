@extends('partials.base')

@section('content')
	<div class="lp_frontpage_articles modern_view">
		@include('partials.pagination')

		<div class="row d-flex align-items-stretch">
			@foreach ($context['lp_frontpage_articles'] as $article)
				<div class="col-xs-12 col-md-6 col-lg-4 mb-4">
					<div class="modern-category roundframe{{ $article['css_class'] ?? '' }}">
						<div class="category-header">
							<div class="category-icon">
								@unless (empty($modSettings['lp_show_author']) || empty($article['author']['avatar']))
									{!! $article['author']['avatar'] !!}
								@else
									<span class="category-default-icon" style="background: {{ ['#FFD700', '#FF4500', '#32CD32', '#1E90FF', '#8A2BE2', '#FF1493', '#00CED1'][($loop->index % 7)] }}">
										@icon('big_image')
									</span>
								@endunless
							</div>

							<div class="category-info">
								<h3>
									<a href="{{ $article['link'] }}">{{ $article['title'] }}</a>
								</h3>

								@unless (empty($article['image']))
									<div class="category-post-image">
										<a href="{{ $article['link'] }}">
											<img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
										</a>
									</div>
								@endunless

								@unless (empty($article['teaser']))
									<p class="category-description">{{ $article['teaser'] }}</p>
								@endunless
							</div>
						</div>

						<div class="category-stats">
							<span class="stat">
								@icon('replies')
								{{ (int) $article['replies']['num'] }}
							</span>
							@unless (empty($modSettings['lp_show_views_and_comments']))
								<span class="stat">
									@icon('views')
									{{ (int) $article['views']['num'] }}
								</span>
							@endunless
						</div>

						<div class="category-footer">
							<span class="author">
								@if ($article['is_new'])
									<span class="new-badge">{{ $txt['new'] }}</span>
								@endif
								{{ $article['author']['name'] ?? $txt['guest_title'] }}
							</span>
							<span class="date">{!! $article['date'] !!}</span>
						</div>
					</div>
				</div>
			@endforeach
		</div>

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
