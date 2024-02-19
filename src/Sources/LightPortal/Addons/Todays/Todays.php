<?php

/**
 * Todays.php
 *
 * @package Todays (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.02.24
 */

namespace Bugo\LightPortal\Addons\Todays;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{RangeField, SelectField};

if (! defined('LP_NAME'))
	die('No direct access...');

class Todays extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-calendar-day';

	public function init(): void
	{
		$this->applyHook('menu_buttons');
	}

	public function menuButtons(array &$buttons): void
	{
		$buttons['calendar']['show'] = Utils::$context['allow_calendar']
			&& empty(Utils::$context['lp_todays_plugin']['hide_calendar_in_menu']);
	}

	public function addSettings(array &$config_vars): void
	{
		$config_vars['todays'][] = [
			'check',
			'hide_calendar_in_menu',
			'subtext' => sprintf(
				Lang::$txt['lp_todays']['hide_calendar_in_menu_subtext'],
				Config::$scripturl . '?action=admin;area=managecalendar;sa=settings'
			)
		];
	}

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'todays')
			return;

		$params = [
			'widget_type' => 'calendar',
			'max_items'   => 1,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'todays')
			return;

		$params = [
			'widget_type' => FILTER_DEFAULT,
			'max_items'   => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'todays')
			return;

		SelectField::make('widget_type', Lang::$txt['lp_todays']['type'])
			->setTab('content')
			->setOptions(array_combine(
				['birthdays', 'holidays', 'events', 'calendar'],
				Lang::$txt['lp_todays']['type_set']
			))
			->setValue(Utils::$context['lp_block']['options']['widget_type']);

		RangeField::make('max_items', Lang::$txt['lp_todays']['max_items'])
			->setAfter(Lang::$txt['lp_todays']['max_items_subtext'])
			->setAttribute('min', 1)
			->setAttribute('max', 100)
			->setValue(Utils::$context['lp_block']['options']['max_items']);
	}

	public function getData(string $type, string $output_method = 'echo')
	{
		return $this->getFromSsi('todays' . ucfirst($type), $output_method);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'todays')
			return;

		$result = $this->getData($parameters['widget_type'], 'array');

		if ($parameters['widget_type'] === 'calendar') {
			if ($result['calendar_holidays'] || $result['calendar_birthdays'] || $result['calendar_events'])
				$this->getData($parameters['widget_type']);
			else
				echo Lang::$txt['lp_todays']['empty_list'];
		} elseif ($parameters['widget_type'] === 'events' && $result) {
			echo '
		<ul>';

			foreach ($result as $event) {
				echo '
			<li>', $event['start_date_local'], ' - ', $event['link'], (
				$event['can_edit'] ? ' <a class="error" href="' . $event['modify_href'] . '">*</a>' : ''
			), '</li>';
			}

			echo '
		</ul>';
		} elseif ($result) {
			if ($parameters['widget_type'] !== 'birthdays' || count($result) <= $parameters['max_items']) {
				$this->getData($parameters['widget_type']);
			} else {
				$visibleItems = array_slice($result, 0, $parameters['max_items']);
				$visibleItems[$parameters['max_items'] - 1]['is_last'] = true;
				$hiddenItems = array_slice($result, $parameters['max_items']);

				foreach ($visibleItems as $member) {
					echo '
		<a href="', Config::$scripturl, '?action=profile;u=', $member['id'], '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>'
				. (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . ($member['is_last'] ? '' : ', ');
				}

				$hiddenContent = '';
				foreach ($hiddenItems as $member) {
					if ($member['is_today'])
						$hiddenContent .= '
		<a href="' . Config::$scripturl . '?action=profile;u=' . $member['id'] . '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>'
				. (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . ($member['is_last'] ? '' : ', ');
				}

				if ($hiddenContent)
					echo Lang::$txt['lp_todays']['and_more'], '
		<details>
			<summary>
				<span>', Lang::getTxt(
					Lang::$txt['lp_todays']['birthdays_set'],
					['count' => count($result) - $parameters['max_items']]
				), '</span>
			</summary>
			<div>', $hiddenContent, '</div>
		</details>';
			}
		} else {
			echo Lang::$txt['lp_todays']['empty_list'];
		}
	}
}
