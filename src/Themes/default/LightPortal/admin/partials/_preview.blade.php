@if ($show_preview && empty($context['post_errors']))
	@if ($preview_type === 'content')
		<div class="cat_bar">
			<h3 class="catbg">{!! $context['preview_title'] ?? $txt['preview'] !!}</h3>
		</div>
		<div class="roundframe noup">
			{!! $context['preview_content'] !!}
		</div>
	@elseif ($preview_type === 'title')
		<div class="cat_bar">
			<h3 class="catbg">{{ $txt['preview'] }}</h3>
		</div>
		<div class="information" style="display: flex">
			<div class="button">{!! $context['preview_title'] !!}</div>
		</div>
	@endif
@else
	<div class="cat_bar">
		<h3 class="catbg">{!! $context['page_area_title'] !!}</h3>
	</div>
@endif
