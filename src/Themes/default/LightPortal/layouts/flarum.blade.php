@set($needLowerCase = in_array($txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']))
@set($labels = ['lp_type_block', 'lp_type_editor', 'lp_type_comment', 'lp_type_parser', 'lp_type_article', 'lp_type_frontpage', 'lp_type_impex', 'lp_type_block_options', 'lp_type_page_options', 'lp_type_icons', 'lp_type_seo', 'lp_type_other', 'lp_type_ssi'])

<div class="row">
	<div class="article_flarum_view col-xs-12">
		{{ show_pagination() }}

		<div class="roundframe">
			<div class="title_bar">
				<h2 class="titlebg">{{ $context['page_title'] }}</h2>
			</div>

			@foreach ($context['lp_frontpage_articles'] as $article)
			<div class="windowbg row">
				<div class="col-xs-12">
					<div class="row">
						<div class="header_img col-xs-2">
							@empty ($article['image'])
							<span>@icon(['big_image'])</span>
							@endempty

							@if (!empty($article['image']))
							<span>
								<img
									class="avatar"
									loading="lazy"
									src="{{ $article['image'] }}"
									alt="{{ $article['title'] }}"
								>
							</span>
							@endif
						</div>

						<div class="header_area col-xs">
							<h3>
								<a href="{{ $article['msg_link'] }}">{{ $article['title'] }}</a>

								@if ($article['is_new'])
								<span class="new_posts">{{ $txt['new'] }}</span>
								@endif
							</h3>

							@if (!empty($article['section']['name']))
							<div class="smalltext hidden-md hidden-lg hidden-xl">
								<span
									class="new_posts {{ $labels[random_int(0, count($labels) - 1)] }}"
									href="{{ $article['section']['link'] }}"
								>{{ $article['section']['name'] }}</span>

								@if (!empty($article['replies']['num']))
								<span style="margin-left: 1em">
									<i class="far fa-comment"></i> {{ $article['replies']['num'] }}
								</span>
								@endif
							</div>
							@endif

							<div class="smalltext">
								@if (!empty($article['replies']['num']))
								<span>@icon(['reply'])</span>
								@endif

								@if (!empty($modSettings['lp_show_author']) && !empty($article['author']))
								<span>{{ $article['author']['name'] ?? $txt['guest_title'] }}</span>
								@endif

								<span style="{{ $needLowerCase ? 'text-transform: lowercase' : '' }}">
									{!! $article['date'] !!}
								</span>
							</div>
						</div>

						<div class="righttext smalltext hidden-xs hidden-sm col-xs-2">
							@if (!empty($article['section']['name']))
							<a
								class="new_posts {{ $labels[random_int(0, count($labels) - 1)] }}"
								href="{{ $article['section']['link'] }}"
							>{{ $article['section']['name'] }}</a>
							@endif

							@if (!empty($article['replies']['num']))
							<div><i class="far fa-comment"></i> {{ $article['replies']['num'] }}</div>
							@endif
						</div>
					</div>
				</div>

				@if (!empty($article['teaser']))
				<div class="col-xs-12">{{ $article['teaser'] }}</div>
				@endif
			</div>
			@endforeach
		</div>

		{{ show_pagination('bottom') }}
	</div>
</div>