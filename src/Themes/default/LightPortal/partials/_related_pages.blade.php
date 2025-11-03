@unless (empty($context['lp_page']['related_pages']))
	<div class="related_pages">
		<div class="cat_bar">
			<h3 class="catbg">{{ $txt['lp_related_pages'] }}</h3>
		</div>
		<div class="list">
			@foreach ($context['lp_page']['related_pages'] as $page)
				<div class="windowbg">
					@unless (empty($page['image']))
						<a href="{{ $page['link'] }}">
							<div class="article_image">
								<img alt="{{ $page['title'] }}" src="{{ $page['image'] }}">
							</div>
						</a>
					@endunless

					<a href="{{ $page['link'] }}">{{ $page['title'] }}</a>
				</div>
			@endforeach
		</div>
	</div>
@endunless
