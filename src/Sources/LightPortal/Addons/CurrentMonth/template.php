<?php

if (function_exists('show_current_month_grid'))
	return;

use Bugo\Compat\{Config, Lang};

function show_current_month_grid(array $data): void
{
	if (empty($data))
		return;

	$calendar_data = &$data;

	echo '
		<table>';

	if (empty($calendar_data['disable_day_titles'])) {
		echo '
			<thead>
				<tr>';

		foreach ($calendar_data['week_days'] as $day)
			echo '
					<th scope="col">', Lang::$txt['days_short'][$day], '</th>';

		echo '
				</tr>
			</thead>';
	}

	foreach ($calendar_data['weeks'] as $week) {
		echo '
			<tbody>
				<tr class="days_wrapper">';

		foreach ($week['days'] as $day) {
			$classes = ['days'];
			if ($day['day']) {
				$classes[] = empty($day['is_today']) ? 'windowbg' : 'calendar_today';

				foreach (['events', 'holidays', 'birthdays'] as $event_type)
					if ($day[$event_type])
						$classes[] = $event_type;
			} else {
				$classes[] = 'disabled';
			}

			echo '
					<td class="', implode(' ', $classes), '">';

			if ($day['day']) {
				if (empty(Config::$modSettings['cal_enabled'])) {
					echo '
						<span class="day_text">', $day['day'], '</span>';
				} else {
					echo '
						<a href="', Config::$scripturl, '?action=calendar;viewlist;year=', $calendar_data['current_year'], ';month=', $calendar_data['current_month'], ';day=', $day['day'], '"><span class="day_text">', $day['day'], '</span></a>';
				}
			}

			echo '
					</td>';
		}

		echo '
				</tr>
			</tbody>';
	}

	echo '
		</table>';
}
