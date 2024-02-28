@empty ($context['lp_active_blocks'])
<div class="col-xs">
@endempty

	<div class="lp_frontpage_articles article_view">
		{{ show_pagination() }}

		@foreach ($context['lp_frontpage_articles'] as $article)
		<div class="col-xs-12 col-sm-6 col-md-{{ $context['lp_frontpage_num_columns'] }}">
			<article class="roundframe{{ $article['css_class'] ?? '' }}">

				@if (!empty($article['image']))
					@if ($article['is_new'])
						<div class="new_hover">
							<div class="new_icon">
								<span class="new_posts">{{ $txt['new'] }}</span>
							</div>
						</div>
					@endif

					@if ($article['can_edit'])
						<div class="info_hover">
							<div class="edit_icon">
								<a href="{{ $article['edit_link'] }}">@icon(['edit', $txt['edit']])</a>
							</div>
						</div>
					@endif

					<div class="card_img"></div>
					<a href="{{ $article['link'] }}">
						<div class="card_img_hover lazy" data-bg="{{ $article['image'] }}"></div>
					</a>
				@endif

				<div class="card_info">
					<span class="card_date smalltext">
						@if (!empty($article['section']['name']))
							<a class="floatleft" href="{{ $article['section']['link'] }}">
								@empty ($article['section']['icon'])
									@icon('category')
								@else
									{!! $article['section']['icon'] !!}
								@endempty

								{{ $article['section']['name'] }}
							</a>
						@endif

						@if ($article['is_new'] && empty($article['image']))
							<span class="new_posts">
								{{ $txt['new'] }}
							</span>
						@endif

						@if (!empty($article['datetime']))
							<time class="floatright" datetime="{{ $article['datetime'] }}">
								@icon('date') {!! $article['date'] !!}
							</time>
						@endif
					</span>

					<h3>
						<a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a>
					</h3>

					@if (!empty($article['teaser']))
						<p>{{ $article['teaser'] }}</p>
					@endif

					<div>
						@if (!empty($article['category']))
							<span class="card_author">
								@icon('category'){{ $article['category'] }}
							</span>
						@endif

						@if (!empty($modSettings['lp_show_author']) && !empty($article['author']))
							@if (!empty($article['author']['id']) && !empty($article['author']['name']))
								<a href="{{ $article['author']['link'] }}" class="card_author">
									@icon('user'){{ $article['author']['name'] }}
								</a>
							@else
								<span class="card_author">{{ $txt['guest_title'] }}</span>
							@endif
						@endif

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