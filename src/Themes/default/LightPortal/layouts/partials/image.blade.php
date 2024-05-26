@unless (empty($article['image']))
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
@endunless