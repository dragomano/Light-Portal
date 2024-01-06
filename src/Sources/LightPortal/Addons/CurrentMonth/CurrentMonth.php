<?php

/**
 * CurrentMonth.php
 *
 * @package CurrentMonth (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.09.23
 */

namespace Bugo\LightPortal\Addons\CurrentMonth;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class CurrentMonth extends Block
{
	public string $icon = 'fas fa-calendar-check';

	public function blockOptions(array &$options): void
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
			'start_day'          => (int) ($this->options['calendar_start_day'] ?? 0),
			'show_birthdays'     => in_array($this->modSettings['cal_showbdays'], [1, 2]),
			'show_events'        => in_array($this->modSettings['cal_showevents'], [1, 2]),
			'show_holidays'      => in_array($this->modSettings['cal_showholidays'], [1, 2]),
			'show_week_num'      => true,
			'short_day_titles'   => (bool) $this->modSettings['cal_short_days'],
			'short_month_titles' => (bool) $this->modSettings['cal_short_months'],
			'show_next_prev'     => (bool) $this->modSettings['cal_prev_next_links'],
			'show_week_links'    => (int) ($this->modSettings['cal_week_links'] ?? 0)
		];

		return getCalendarGrid(date_format($start_object, 'Y-m-d'), $calendarOptions);
	}

	public function prepareContent(object $data): void
	{
		if ($data->type !== 'current_month')
			return;

		$calendar_data = $this->cache('current_month_addon_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData');

		if ($calendar_data) {
			$calendar_data['block_id'] = $data->block_id;

			$title = $this->txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'];

			// Auto title
			if (isset($this->context['preview_title']) && empty($this->context['preview_title'])) {
				$this->context['preview_title'] = $title;
			} elseif ($data->block_id && empty($this->context['lp_active_blocks'][$data->block_id]['title'][$this->user_info['language']])) {
				$this->context['lp_active_blocks'][$data->block_id]['title'][$this->user_info['language']] = $title;
			}

			$this->setTemplate();

			show_current_month_grid($calendar_data);
		}
	}
}
