<?php

/**
 * Todays.php
 *
 * @package Todays (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.12.21
 */

namespace Bugo\LightPortal\Addons\Todays;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class Todays extends Plugin
{
	public string $icon = 'fas fa-calendar-day';

	public function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
	}

	public function menuButtons(array &$buttons)
	{
		global $context, $modSettings;

		$buttons['calendar']['show'] = ! empty($context['allow_calendar']) && empty($modSettings['lp_todays_addon_hide_calendar_in_menu']);
	}

	public function addSettings(array &$config_vars)
	{
		global $txt, $scripturl;

		$config_vars['todays'][] = array(
			'check',
			'hide_calendar_in_menu',
			'subtext' => sprintf($txt['lp_todays']['hide_calendar_in_menu_subtext'], $scripturl . '?action=admin;area=managecalendar;sa=settings')
		);
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

		$parameters['widget_type'] = FILTER_SANITIZE_STRING;
		$parameters['max_items']   = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'todays')
			return;

		$context['posting_fields']['widget_type']['label']['text'] = $txt['lp_todays']['type'];
		$context['posting_fields']['widget_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'widget_type'
			),
			'options' => array(),
			'tab' => 'content'
		);

		$types = array_combine(array('birthdays', 'holidays', 'events', 'calendar'), $txt['lp_todays']['type_set']);

		foreach ($types as $key => $value) {
			$context['posting_fields']['widget_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['widget_type']
			);
		}

		$context['posting_fields']['max_items']['label']['text'] = $txt['lp_todays']['max_items'];
		$context['posting_fields']['max_items']['input'] = array(
			'type' => 'number',
			'after' => $txt['lp_todays']['max_items_subtext'],
			'attributes' => array(
				'id'    => 'max_items',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['max_items']
			)
		);
	}

	public function getData(string $type, string $output_method = 'echo')
	{
		$this->loadSsi();

		$funcName = 'ssi_todays' . ucfirst($type);

		return function_exists($funcName) ? $funcName($output_method) : '';
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $txt, $scripturl;

		if ($type !== 'todays')
			return;

		$result = $this->getData($parameters['widget_type'], 'array');

		if ($parameters['widget_type'] == 'calendar') {
			if (! empty($result['calendar_holidays']) || ! empty($result['calendar_birthdays']) || ! empty($result['calendar_events']))
				$this->getData($parameters['widget_type']);
			else
				echo $txt['lp_todays']['empty_list'];
		} elseif (! empty($result)) {
			if ($parameters['widget_type'] != 'birthdays' || count($result) <= $parameters['max_items']) {
				$this->getData($parameters['widget_type']);
			} else {
				$visibleItems = array_slice($result, 0, $parameters['max_items']);
				$visibleItems[$parameters['max_items'] - 1]['is_last'] = true;
				$hiddenItems = array_slice($result, $parameters['max_items']);

				foreach ($visibleItems as $member) {
					echo '
		<a href="', $scripturl, '?action=profile;u=', $member['id'], '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>' . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . ($member['is_last'] ? '' : ', ');
				}

				$hiddenContent = '';
				foreach ($hiddenItems as $member) {
					$hiddenContent .= '
		<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>' . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . ($member['is_last'] ? '' : ', ');
				}

				// HTML5 spoiler
				if (! empty($hiddenContent))
					echo $txt['lp_todays']['and_more'], '
		<details>
			<summary>
				<span>', Helper::getSmartContext($txt['lp_todays']['birthdays_set'], ['count' => count($result) - $parameters['max_items']]), '</span>
			</summary>
			<div>', $hiddenContent, '</div>
		</details>';
			}
		} else {
			echo $txt['lp_todays']['empty_list'];
		}
	}
}
