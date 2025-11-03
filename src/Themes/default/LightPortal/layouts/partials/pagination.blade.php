@unless (empty($context['lp_frontpage_articles']))
	@php
		$position ??= 'top';
		$show_on_top = $position === 'top' && ! empty($modSettings['lp_show_pagination']);
		$show_on_bottom = $position === 'bottom' && (empty($modSettings['lp_show_pagination']) || $modSettings['lp_show_pagination'] == 1);
	@endphp

	@if (! empty($context['page_index']) && ($show_on_top || $show_on_bottom))
		<div class="col-xs-12 centertext">
			<div class="pagesection">{!! $context['page_index'] !!}</div>
		</div>
	@endif
@endunless
