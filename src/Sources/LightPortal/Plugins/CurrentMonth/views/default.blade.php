<table
	class="table_grid"
	role="grid"
	aria-label="{{ ($txt['months_titles'][$data['current_month']] ?? '') . ' ' . ($data['current_year'] ?? '') }}"
>
	@if (empty($data['disable_day_titles']))
		<thead>
			<tr class="title_bar">
				@foreach ($data['week_days'] as $day)
					<th scope="col">
						{{ $txt['days_short'][$day] ?? $day }}
					</th>
				@endforeach
			</tr>
		</thead>
	@endif

	@foreach ($data['weeks'] as $week)
		<tbody>
			<tr class="days_wrapper">
				@foreach ($week['days'] as $day)
					@php
						$classes = ['days'];

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

					<td class="{{ implode(' ', $classes) }}">
						@unless (empty($day['day']))
							@if (empty($modSettings['cal_enabled']))
								<span class="day_text">{{ $day['day'] }}</span>
							@else
								<a href="{{ $scripturl }}?action=calendar;viewlist;year={{ $data['current_year'] }};month={{ $data['current_month'] }};day={{ $day['day'] }}">
									<span class="day_text">{{ $day['day'] }}</span>
								</a>
							@endif
						@endunless
					</td>
				@endforeach
			</tr>
		</tbody>
	@endforeach
</table>
