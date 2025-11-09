@unless (empty($context['lp_load_page_stats']))
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">
		{{ $context['lp_load_page_stats'] }}
	</div>
@endunless

@unless (empty($context['lp_portal_queries']))
	@php $totalQueries = count($context['lp_portal_queries']); @endphp

	@if ($totalQueries > 0)
		<div class="cat_bar" style="margin-top: 4px">
			<h3 class="catbg">{{ sprintf($txt['debug_queries_used'], $totalQueries) }}</h3>
		</div>
		<div class="debug-queries-container roundframe">
			@php $index = 1; @endphp
			@foreach ($context['lp_portal_queries'] as $profile)
				@php
					$sqlText = htmlspecialchars($profile['sql']);
					$time = number_format($profile['elapse'], 6);
					$location = 'unknown';

					if (isset($profile['backtrace']) && $profile['backtrace']) {
						$bt = $profile['backtrace'];
						$location = sprintf(
							'%s:%d',
							basename($bt['file'] ?? 'unknown'),
							$bt['line'] ?? 0
						);
					}
				@endphp
				<div class="query-item windowbg">
					<div class="query-header">
						<span class="query-index">#{{ $index++ }}</span>
						<span class="query-time">{{ $time }} {{ $txt['seconds'] }}</span>
						<span class="query-location">{{ $location }}</span>
					</div>
					<div class="query-sql">{{ $sqlText }}</div>
				</div>
			@endforeach
		</div>
	@endif
@endunless
