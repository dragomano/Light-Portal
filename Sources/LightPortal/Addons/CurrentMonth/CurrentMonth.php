<?php

/**
 * CurrentMonth.php
 *
 * @package CurrentMonth (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 07.01.22
 */

namespace Bugo\LightPortal\Addons\CurrentMonth;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class CurrentMonth extends Plugin
{
	public string $icon = 'fas fa-calendar-check';

	public function blockOptions(array &$options)
	{
		$options['current_month']['no_content_class'] = true;
	}

	public function getData(): array
	{
		$this->require('Subs-Calendar');

		$today = getTodayInfo();
		$year  = $today['year'];
		$month = $today['month'];
		$day   = $today['day'];

		$start_object = checkdate($month, $day, $year) === true
			? date_create(implode('-', [$year, $month, $day]))
			: date_create(implode('-', [$today['year'], $today['month'], $today['day']]));

		$calendarOptions = [
			'start_day'          => $this->options['calendar_start_day'] ?: 0,
			'show_birthdays'     => in_array($this->modSettings['cal_showbdays'], [1, 2]),
			'show_events'        => in_array($this->modSettings['cal_showevents'], [1, 2]),
			'show_holidays'      => in_array($this->modSettings['cal_showholidays'], [1, 2]),
			'show_week_num'      => true,
			'short_day_titles'   => (bool) $this->modSettings['cal_short_days'],
			'short_month_titles' => (bool) $this->modSettings['cal_short_months'],
			'show_next_prev'     => (bool) $this->modSettings['cal_prev_next_links'],
			'show_week_links'    => $this->modSettings['cal_week_links'] ?? 0
		];

		return getCalendarGrid(date_format($start_object, 'Y-m-d'), $calendarOptions);
	}

	private function showCurrentMonthGrid(array $data)
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
							<th scope="col">', $this->txt['days_short'][$day], '</th>';

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
					if (empty($this->modSettings['cal_enabled'])) {
						echo '
								<span class="day_text">', $day['day'], '</span>';
					} else {
						echo '
								<a href="', $this->scripturl, '?action=calendar;viewlist;year=', $calendar_data['current_year'], ';month=', $calendar_data['current_month'], ';day=', $day['day'], '"><span class="day_text">', $day['day'], '</span></a>';
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

	public function prepareContent(string $type, int $block_id, int $cache_time)
	{
		if ($type !== 'current_month')
			return;

		$calendar_data = $this->cache('current_month_addon_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

		if ($calendar_data) {
			$calendar_data['block_id'] = $block_id;

			$title = $this->txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'];

			// Auto title
			if (isset($this->context['preview_title']) && empty($this->context['preview_title'])) {
				$this->context['preview_title'] = $title;
			} elseif ($block_id && empty($this->context['lp_active_blocks'][$block_id]['title'][$this->user_info['language']])) {
				$this->context['lp_active_blocks'][$block_id]['title'][$this->user_info['language']] = $title;
			}

			$this->showCurrentMonthGrid($calendar_data);
		}
	}
}
