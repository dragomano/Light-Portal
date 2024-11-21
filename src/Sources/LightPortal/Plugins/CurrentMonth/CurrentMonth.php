<?php

/**
 * @package CurrentMonth (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.11.24
 */

namespace Bugo\LightPortal\Plugins\CurrentMonth;

use Bugo\Compat\{Config, Lang, Theme, User, Utils};
use Bugo\Compat\Actions\Calendar;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
	die('No direct access...');

class CurrentMonth extends Block
{
	public string $icon = 'fas fa-calendar-check';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params['no_content_class'] = true;
	}

	public function getData(): array
	{
		$today = Calendar::getTodayInfo();
		$year  = $today['year'];
		$month = $today['month'];
		$day   = $today['day'];

		$startObject = checkdate($month, $day, $year) === true
			? date_create(implode('-', [$year, $month, $day]))
			: date_create(implode('-', [$today['year'], $today['month'], $today['day']]));

		$options = [
			'start_day'          => (int) (Theme::$current->options['calendar_start_day'] ?? 0),
			'show_birthdays'     => in_array(Config::$modSettings['cal_showbdays'], [1, 2]),
			'show_events'        => in_array(Config::$modSettings['cal_showevents'], [1, 2]),
			'show_holidays'      => in_array(Config::$modSettings['cal_showholidays'], [1, 2]),
			'show_week_num'      => true,
			'short_day_titles'   => (bool) Config::$modSettings['cal_short_days'],
			'short_month_titles' => (bool) Config::$modSettings['cal_short_months'],
			'show_next_prev'     => (bool) Config::$modSettings['cal_prev_next_links'],
			'show_week_links'    => (int) (Config::$modSettings['cal_week_links'] ?? 0)
		];

		return Calendar::getCalendarGrid(date_format($startObject, 'Y-m-d'), $options, has_picker: false);
	}

	public function prepareContent(Event $e): void
	{
		$id = $e->args->id;

		$calendarData = $this->cache($this->name . '_addon_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData');

		if ($calendarData) {
			$calendarData['block_id'] = $id;

			$title = Lang::$txt['months_titles'][$calendarData[$this->name]] . ' ' . $calendarData['current_year'];

			// Auto title
			if (isset(Utils::$context['preview_title']) && empty(Utils::$context['preview_title'])) {
				Utils::$context['preview_title'] = $title;
			} elseif (
				$id
				&& empty(Utils::$context['lp_active_blocks'][$id]['titles'][User::$info['language']])
			) {
				Utils::$context['lp_active_blocks'][$id]['titles'][User::$info['language']] = $title;
			}

			$this->setTemplate();

			show_current_month_grid($calendarData);
		}
	}
}
