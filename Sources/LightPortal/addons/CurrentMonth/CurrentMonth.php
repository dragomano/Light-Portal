<?php

/**
 * CurrentMonth
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\CurrentMonth;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class CurrentMonth extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-calendar-check';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['current_month']['no_content_class'] = true;
	}

	/**
	 * Get calendar data for the current month
	 *
	 * Получаем данные календаря за текущий месяц
	 *
	 * @return array
	 */
	public function getData(): array
	{
		global $options, $modSettings;

		Helpers::require('Subs-Calendar');

		$today = getTodayInfo();
		$year  = $today['year'];
		$month = $today['month'];
		$day   = $today['day'];

		$start_object = checkdate($month, $day, $year) === true ? date_create(implode('-', array($year, $month, $day))) : date_create(implode('-', array(
			$today['year'],
			$today['month'],
			$today['day']
		)));

		$calendarOptions = array(
			'start_day'          => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
			'show_birthdays'     => in_array($modSettings['cal_showbdays'], array(1, 2)),
			'show_events'        => in_array($modSettings['cal_showevents'], array(1, 2)),
			'show_holidays'      => in_array($modSettings['cal_showholidays'], array(1, 2)),
			'show_week_num'      => true,
			'short_day_titles'   => !empty($modSettings['cal_short_days']),
			'short_month_titles' => !empty($modSettings['cal_short_months']),
			'show_next_prev'     => !empty($modSettings['cal_prev_next_links']),
			'show_week_links'    => $modSettings['cal_week_links'] ?? 0
		);

		return getCalendarGrid(date_format($start_object, 'Y-m-d'), $calendarOptions);
	}

	/**
	 * Display a monthly calendar grid
	 *
	 * Отображаем календарную сетку текущего месяца
	 *
	 * @param array $data
	 * @return void|bool Returns false if the grid doesn't exist
	 */
	private function showCurrentMonthGrid(array $data)
	{
		global $txt, $modSettings, $scripturl;

		if (empty($data))
			return false;

		$calendar_data = &$data;

		echo '
				<table>';

		if (empty($calendar_data['disable_day_titles'])) {
			echo '
					<thead>
						<tr>';

			foreach ($calendar_data['week_days'] as $day)
				echo '
							<th scope="col">', $txt['days_short'][$day], '</th>';

			echo '
						</tr>
					</thead>';
		}

		foreach ($calendar_data['weeks'] as $week) {
			echo '
					<tbody>
						<tr class="days_wrapper">';

			foreach ($week['days'] as $day) {
				$classes = array('days');
				if (!empty($day['day'])) {
					$classes[] = !empty($day['is_today']) ? 'calendar_today' : 'windowbg';

					foreach (array('events', 'holidays', 'birthdays') as $event_type)
						if (!empty($day[$event_type]))
							$classes[] = $event_type;
				} else {
					$classes[] = 'disabled';
				}

				echo '
							<td class="', implode(' ', $classes), '">';

				if (!empty($day['day'])) {
					if (!empty($modSettings['cal_enabled'])) {
						echo '
								<a href="', $scripturl, '?action=calendar;viewlist;year=', $calendar_data['current_year'], ';month=', $calendar_data['current_month'], ';day=', $day['day'], '"><span class="day_text">', $day['day'], '</span></a>';
					} else {
						echo '
								<span class="day_text">', $day['day'], '</span>';
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

	/**
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time)
	{
		global $user_info, $txt, $context;

		if ($type !== 'current_month')
			return;

		$calendar_data = Helpers::cache('current_month_addon_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

		if (!empty($calendar_data)) {
			$calendar_data['block_id'] = $block_id;

			$title = $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'];

			// Auto title
			if (isset($context['preview_title']) && empty($context['preview_title'])) {
				$context['preview_title'] = $title;
			} elseif (!empty($block_id) && empty($context['lp_active_blocks'][$block_id]['title'][$user_info['language']])) {
				$context['lp_active_blocks'][$block_id]['title'][$user_info['language']] = $title;
			}

			$this->showCurrentMonthGrid($calendar_data);
		}
	}
}
