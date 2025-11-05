@unless (empty($modSettings['lp_show_layout_switcher']) && empty($modSettings['lp_show_sort_dropdown']))
	@unless (empty($context['lp_frontpage_articles']) || empty($context['lp_frontpage_layouts']))
		<div class="windowbg frontpage_toolbar">
			<div class="floatleft">@icon('views')</div>
			<div class="floatright">
				<form method="post">
					@include('partials._layout_switcher')
					@include('partials._sort_dropdown')
				</form>
			</div>
		</div>
	@endunless
@endunless
