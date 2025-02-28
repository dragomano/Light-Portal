@extends('partials.base')

@section('content')
	<div class="lp_frontpage_articles article_alt2_view">
		@include('partials.pagination')

		@foreach ($context['lp_frontpage_articles'] as $article)
			<article class="descbox">

				@unless (empty($article['image']))
					<a class="article_image_link" href="{{ $article['link'] }}">
						<div class="lazy" data-bg="{{ $article['image'] }}"></div>
					</a>
				@endunless

				<div class="article_body">
					<div>
						<header>

							@unless (empty($article['datetime']))
								<time datetime="{{ $article['datetime'] }}">
									@icon('date') {!! $article['date'] !!}
								</time>
							@endunless

							<h3><a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a></h3>
						</header>

						@unless (empty($article['teaser']))
							<section>
								<p>{{ $article['teaser'] }}</p>
							</section>
						@endunless

					</div>

					@if (!empty($modSettings['lp_show_author']) && !empty($article['author']))
						<footer>

							@unless (empty($article['author']['avatar']))
								{!! $article['author']['avatar'] !!}
							@endunless

							<span>
									@if (!empty($article['author']['id']) && !empty($article['author']['name']))
									<a href="{{ $article['author']['link'] }}">{{ $article['author']['name'] }}</a>
								@else
									<span>{{ $txt['guest_title'] }}</span>
								@endif
								</span>
						</footer>
					@endif

				</div>
			</article>
		@endforeach

		@include('partials.pagination', ['position' => 'bottom'])
	</div>
@endsection
