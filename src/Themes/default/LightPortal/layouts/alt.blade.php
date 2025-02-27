@extends('partials.base')

@section('content')
	<div class="lp_frontpage_articles article_alt_view">
		@include('partials.pagination')

		@foreach ($context['lp_frontpage_articles'] as $article)
			<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
				<article class="roundframe">
					<header>
						<div class="title_bar">
							<h3>
								<a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a>

								@if ($article['is_new'])
									<span class="new_posts">{{ $txt['new'] }}</span>
								@endif
							</h3>
						</div>
						<div>
							@unless (empty($modSettings['lp_show_views_and_comments']))
								<span class="floatleft">
									@unless (empty($article['views']['num']))
										@icon(['views', $article['views']['title']])
										{{ $article['views']['num'] }}
									@endunless

									@unless (empty($article['views']['after']))
										{!! $article['views']['after'] !!}
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

							@unless (empty($article['section']['name']))
								<a class="floatright" href="{{ $article['section']['link'] }}">
									@empty ($article['section']['icon'])
										@icon('category')
									@else
										{!! $article['section']['icon'] !!}
									@endempty

									{{ $article['section']['name'] }}
								</a>
							@endunless
						</div>

						@unless (empty($article['image']))
							<img
								class="lazy"
								data-src="{{ $article['image'] }}"
								alt="{{ $article['title'] }}"
							>
						@endunless
					</header>

					@unless (empty($article['teaser']))
						<div class="article_body">
							<p>{{ $article['teaser'] }}</p>
						</div>
					@endunless

					<div class="article_footer">
						<div class="centertext">
							<a class="bbc_link" href="{{ $article['link'] }}">{{ $txt['lp_read_more'] }}</a>
						</div>
						<div class="centertext">

							@unless (empty($article['datetime']))
								<time datetime="{{ $article['datetime'] }}">
									@icon('date') {!! $article['date'] !!}
								</time>
							@endunless

							@if (!empty($modSettings['lp_show_author']) && !empty($article['author']))
								@if (!empty($article['author']['id']) && !empty($article['author']['name']))
									| @icon('user')
									<a href="{{ $article['author']['link'] }}" class="card_author">
										{{ $article['author']['name'] }}
									</a>
								@else
									| <span class="card_author">{{ $txt['guest_title'] }}</span>
								@endif
							@endif
						</div>
					</div>
				</article>
			</div>
		@endforeach

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
