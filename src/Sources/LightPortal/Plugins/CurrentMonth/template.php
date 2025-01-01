<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;

function show_current_month_grid(array $data): void
{
	if (empty($data))
		return;

	$calendarData = &$data;

	echo '
		<table>';

	if (empty($calendarData['disable_day_titles'])) {
		echo '
			<thead>
				<tr>';

		foreach ($calendarData['week_days'] as $day)
			echo '
					<th scope="col">', Lang::$txt['days_short'][$day], '</th>';

		echo '
				</tr>
			</thead>';
	}

	foreach ($calendarData['weeks'] as $week) {
		echo '
			<tbody>
				<tr class="days_wrapper">';

		foreach ($week['days'] as $day) {
			$classes = ['days'];
			if ($day['day']) {
				$classes[] = empty($day['is_today']) ? 'windowbg' : 'calendar_today';

				foreach (['events', 'holidays', 'birthdays'] as $type) {
					$day[$type] && $classes[] = $type;
				}
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
						<a href="', Config::$scripturl, '?action=calendar;viewlist;year=', $calendarData['current_year'], ';month=', $calendarData['current_month'], ';day=', $day['day'], '"><span class="day_text">', $day['day'], '</span></a>';
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
