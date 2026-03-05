<div
	class="calendar_grid"
	role="grid"
	aria-label="{{ ($txt['months_titles'][$data['current_month']] ?? '') . ' ' . ($data['current_year'] ?? '') }}"
>
	@if (empty($data['disable_day_titles']))
		<div class="calendar_grid__header" role="row">
			@foreach ($data['week_days'] as $day)
				<div class="calendar_grid__weekday" role="columnheader">
					{{ $txt['days_short'][$day] ?? $day }}
				</div>
			@endforeach
		</div>
	@endif

	<div class="calendar_grid__body">
		@foreach ($data['weeks'] as $week)
			@foreach ($week['days'] as $day)
				@php
					$classes = ['calendar_grid__day'];

					if (empty($day['day'])) {
						$classes[] = 'disabled';
					} else {
						$classes[] = empty($day['is_today']) ? 'windowbg' : 'calendar_today';

						foreach (['events', 'holidays', 'birthdays'] as $type) {
							if (! empty($day[$type])) {
								$classes[] = $type;
							}
						}
					}
				@endphp

				<div class="{{ implode(' ', $classes) }}" role="gridcell">
					@unless (empty($day['day']))
						@if (empty($modSettings['cal_enabled']))
							<span class="day_text">{{ $day['day'] }}</span>
						@else
							<a href="{{ $scripturl }}?action=calendar;viewlist;year={{ $data['current_year'] }};month={{ $data['current_month'] }};day={{ $day['day'] }}">
								<span class="day_text">{{ $day['day'] }}</span>
							</a>
						@endif
					@endunless
				</div>
			@endforeach
		@endforeach
	</div>
</div>
