<?php

/**
 * Todays.php
 *
 * @package Todays (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 07.04.23
 */

namespace Bugo\LightPortal\Addons\Todays;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class Todays extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-calendar-day';

	public function init()
	{
		$this->applyHook('menu_buttons');
	}

	public function menuButtons(array &$buttons)
	{
		$buttons['calendar']['show'] = $this->context['allow_calendar'] && empty($this->context['lp_todays_plugin']['hide_calendar_in_menu']);
	}

	public function addSettings(array &$config_vars)
	{
		$config_vars['todays'][] = [
			'check',
			'hide_calendar_in_menu',
			'subtext' => sprintf($this->txt['lp_todays']['hide_calendar_in_menu_subtext'], $this->scripturl . '?action=admin;area=managecalendar;sa=settings')
		];
	}

	public function blockOptions(array &$options)
	{
		$options['todays']['parameters'] = [
			'widget_type' => 'calendar',
			'max_items'   => 1,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'todays')
			return;

		$parameters['widget_type'] = FILTER_DEFAULT;
		$parameters['max_items']   = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'todays')
			return;

		$this->context['posting_fields']['widget_type']['label']['text'] = $this->txt['lp_todays']['type'];
		$this->context['posting_fields']['widget_type']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'widget_type'
			],
			'options' => [],
			'tab' => 'content'
		];

		$types = array_combine(['birthdays', 'holidays', 'events', 'calendar'], $this->txt['lp_todays']['type_set']);

		foreach ($types as $key => $value) {
			$this->context['posting_fields']['widget_type']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['widget_type']
			];
		}

		$this->context['posting_fields']['max_items']['label']['text'] = $this->txt['lp_todays']['max_items'];
		$this->context['posting_fields']['max_items']['input'] = [
			'type' => 'number',
			'after' => $this->txt['lp_todays']['max_items_subtext'],
			'attributes' => [
				'id'    => 'max_items',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['max_items']
			]
		];
	}

	public function getData(string $type, string $output_method = 'echo')
	{
		return $this->getFromSsi('todays' . ucfirst($type), $output_method);
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'todays')
			return;

		$result = $this->getData($parameters['widget_type'], 'array');

		if ($parameters['widget_type'] === 'calendar') {
			if ($result['calendar_holidays'] || $result['calendar_birthdays'] || $result['calendar_events'])
				$this->getData($parameters['widget_type']);
			else
				echo $this->txt['lp_todays']['empty_list'];
		} elseif ($parameters['widget_type'] === 'events' && $result) {
			echo '
		<ul>';

			foreach ($result as $event) {
				echo '
			<li>', $event['start_date_local'], ' - ', $event['link'], ($event['can_edit'] ? ' <a href="' . $event['modify_href'] . '" style="color: #ff0000;">*</a>' : ''), '</li>';
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
		<a href="', $this->scripturl, '?action=profile;u=', $member['id'], '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>' . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . ($member['is_last'] ? '' : ', ');
				}

				$hiddenContent = '';
				foreach ($hiddenItems as $member) {
					$hiddenContent .= '
		<a href="' . $this->scripturl . '?action=profile;u=' . $member['id'] . '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>' . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . ($member['is_last'] ? '' : ', ');
				}

				// HTML5 spoiler
				if ($hiddenContent)
					echo $this->txt['lp_todays']['and_more'], '
		<details>
			<summary>
				<span>', $this->translate($this->txt['lp_todays']['birthdays_set'], ['count' => count($result) - $parameters['max_items']]), '</span>
			</summary>
			<div>', $hiddenContent, '</div>
		</details>';
			}
		} else {
			echo $this->txt['lp_todays']['empty_list'];
		}
	}
}
