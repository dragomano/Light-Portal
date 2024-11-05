<?php

/**
 * @package Events (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\Events;

use Bugo\Compat\{Actions\Calendar, Lang, User, Utils};
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\RangeField;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Events extends Block
{
	public string $icon = 'fas fa-calendar-check';

	public function prepareBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'events')
			return;

		$e->args->params = [
			'show_birthdays'  => false,
			'show_holidays'   => false,
			'show_events'     => true,
			'days_in_future'  => 7,
			'update_interval' => 600,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'events')
			return;

		$e->args->params = [
			'show_birthdays'  => FILTER_VALIDATE_BOOLEAN,
			'show_holidays'   => FILTER_VALIDATE_BOOLEAN,
			'show_events'     => FILTER_VALIDATE_BOOLEAN,
			'days_in_future'  => FILTER_VALIDATE_INT,
			'update_interval' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'events')
			return;

		Lang::load('ManageCalendar');

		CheckboxField::make('show_birthdays', Lang::$txt['setting_cal_showbdays'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_birthdays']);

		CheckboxField::make('show_holidays', Lang::$txt['setting_cal_showholidays'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_holidays']);

		CheckboxField::make('show_events', Lang::$txt['setting_cal_showevents'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_events']);

		RangeField::make('days_in_future', Lang::$txt['lp_events']['days_in_future'])
			->setAttribute('max', 60)
			->setValue(Utils::$context['lp_block']['options']['days_in_future']);

		NumberField::make('update_interval', Lang::$txt['lp_events']['update_interval'])
			->setAttribute('min', 0)
			->setValue(Utils::$context['lp_block']['options']['update_interval']);
	}

	public function changeIconSet(Event $e): void
	{
		$e->args->set['cake']  = 'fas fa-cake-candles';
		$e->args->set['event'] = 'fas fa-calendar-days';
	}

	public function getData(array $parameters): array
	{
		$now = time();
		$todayDate = date('Y-m-d', $now);

		$futureDate = empty($parameters['days_in_future'])
			? $todayDate
			: date('Y-m-d', ($now + $parameters['days_in_future'] * 24 * 60 * 60));

		$options = [
			'show_birthdays' => (bool) $parameters['show_birthdays'],
			'show_holidays'  => (bool) $parameters['show_holidays'],
			'show_events'    => (bool) $parameters['show_events'],
		];

		return Calendar::getCalendarList($todayDate, $futureDate, $options);
	}

	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'events')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$data = $this->cache('events_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		$this->setTemplate();

		template_show_events($data);
	}
}
