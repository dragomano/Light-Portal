@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	<div class="lp_frontpage_articles article_alt_view">
		{{ show_pagination() }}

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
						@if (!empty($modSettings['lp_show_views_and_comments']))
						<span class="floatleft">
							@if (!empty($article['views']['num']))
								@icon(['views', $article['views']['title']])
								{{ $article['views']['num'] }}
							@endif

							@if (!empty($article['views']['after']))
								{!! $article['views']['after'] !!}
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

						@if (!empty($article['section']['name']))
						<a class="floatright" href="{{ $article['section']['link'] }}">
							@icon('category') {{ $article['section']['name'] }}
						</a>
						@endif
					</div>

					@if (!empty($article['image']))
					<img
						class="lazy"
						data-src="{{ $article['image'] }}"
						alt="{{ $article['title'] }}"
					>
					@endif
				</header>

				@if (!empty($article['teaser']))
				<div class="article_body">
					<p>{{ $article['teaser'] }}</p>
				</div>
				@endif

				<div class="article_footer">
					<div class="centertext">
						<a class="bbc_link" href="{{ $article['link'] }}">{{ $txt['lp_read_more'] }}</a>
					</div>
					<div class="centertext">

						@if (!empty($article['datetime']))
						<time datetime="{$article['datetime']}">
							@icon('date') {!! $article['date'] !!}
						</time>
						@endif

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

		{{ show_pagination('bottom') }}
	</div>

@empty ($context['lp_active_blocks'])
</div>
@endempty