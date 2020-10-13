<?php

namespace Bugo\LightPortal\Addons\CurrentMonth;

use Bugo\LightPortal\Helpers;

/**
 * CurrentMonth
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class CurrentMonth
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-calendar-check';

	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['current_month'] = array(
			'no_content_class' => static::$no_content_class
		);
	}

	/**
	 * Get calendar data for the current month
	 *
	 * Получаем данные календаря за текущий месяц
	 *
	 * @return array
	 */
	public static function getData()
	{
		global $sourcedir, $options, $modSettings;

		require_once($sourcedir . '/Subs-Calendar.php');

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
			'show_week_links'    => isset($modSettings['cal_week_links']) ? $modSettings['cal_week_links'] : 0
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
	private static function showCurrentMonthGrid($data)
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
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time)
	{
		global $user_info, $txt, $context;

		if ($type !== 'current_month')
			return;

		$calendar_data = Helpers::cache('current_month_addon_u' . $user_info['id'], 'getData', __CLASS__, $cache_time);

		if (!empty($calendar_data)) {
			ob_start();

			$calendar_data['block_id'] = $block_id;

			$title = $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'];

			// Auto title
			if (isset($context['preview_title']) && empty($context['preview_title']))
				$context['preview_title'] = $title;
			elseif (empty($context['lp_active_blocks'][$block_id]['title'][$user_info['language']]))
				$context['lp_active_blocks'][$block_id]['title'][$user_info['language']] = $title;

			self::showCurrentMonthGrid($calendar_data);

			$content = ob_get_clean();
		}
	}
}
