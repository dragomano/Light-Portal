@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	<div class="lp_frontpage_articles article_alt2_view">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
		<article class="descbox">

			@if (!empty($article['image']))
			<a class="article_image_link" href="{{ $article['link'] }}">
				<div class="lazy" data-bg="{{ $article['image'] }}"></div>
			</a>
			@endif

			<div class="article_body">
				<div>
					<header>

						@if (!empty($article['datetime']))
						<time datetime="{$article['datetime']}">
							@icon(['date']) {!! $article['date'] !!}
						</time>
						@endif

						<h3><a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a></h3>
					</header>

					@if (!empty($article['teaser']))
					<section>
						<p>{{ $article['teaser'] }}</p>
					</section>
					@endif

				</div>

				@if (!empty($modSettings['lp_show_author']) && !empty($article['author']))
				<footer>

					@if (!empty($article['author']['avatar']))
						{!! $article['author']['avatar'] !!}
					@endif

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

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty