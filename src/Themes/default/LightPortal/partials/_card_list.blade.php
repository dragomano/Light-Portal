<div class="cat_bar">
	<h3 class="catbg">
		{{ $context['page_title'] }}

		@if ($context['user']['is_admin'] && isset($context['lp_category_edit_link']))
			<a class="floatright" href="{{ $context['lp_category_edit_link'] }}">
				@icon('edit')
				<span class="hidden-xs">{{ $txt['edit'] }}</span>
			</a>
		@endif
	</h3>
</div>

@unless (empty($context['description']))
	<div class="information">
		<div class="floatleft">{!! $context['description'] !!}</div>
	</div>
@endunless
