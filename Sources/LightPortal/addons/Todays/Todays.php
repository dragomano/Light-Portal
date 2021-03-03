<?php

namespace Bugo\LightPortal\Addons\Todays;

use Bugo\LightPortal\Helpers;

/**
 * Todays
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Todays
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-calendar-day';

	/**
	 * @var string
	 */
	private $type = 'calendar';

	/**
	 * @var int
	 */
	private $max_items = 1;

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
	}

	/**
	 * Hide Calendar in the main menu
	 *
	 * Убираем Календарь из главного меню
	 *
	 * @param array $buttons
	 * @return void
	 */
	public function menuButtons(&$buttons)
	{
		global $context, $modSettings;

		$buttons['calendar']['show'] = !empty($context['allow_calendar']) && empty($modSettings['lp_todays_addon_hide_calendar_in_menu']);
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $txt, $scripturl;

		$config_vars[] = array(
			'check',
			'lp_todays_addon_hide_calendar_in_menu',
			'subtext' => sprintf($txt['lp_todays_addon_hide_calendar_in_menu_subtext'], $scripturl . '?action=admin;area=managecalendar;sa=settings')
		);
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['todays']['parameters']['widget_type'] = $this->type;
		$options['todays']['parameters']['max_items']   = $this->max_items;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'todays')
			return;

		$parameters['widget_type'] = FILTER_SANITIZE_STRING;
		$parameters['max_items']   = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'todays')
			return;

		$context['posting_fields']['widget_type']['label']['text'] = $txt['lp_todays_addon_type'];
		$context['posting_fields']['widget_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'widget_type'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_todays_addon_type_set'] as $key => $value) {
			$context['posting_fields']['widget_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['widget_type']
			);
		}

		$context['posting_fields']['max_items']['label']['text'] = $txt['lp_todays_addon_max_items'];
		$context['posting_fields']['max_items']['input'] = array(
			'type' => 'number',
			'after' => $txt['lp_todays_addon_max_items_subtext'],
			'attributes' => array(
				'id'    => 'max_items',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['max_items']
			)
		);
	}

	/**
	 * Get the list of the content we need
	 *
	 * Получаем список нужного нам контента
	 *
	 * @param string $type
	 * @param string $output_method
	 * @return string
	 */
	public function getData($type, $output_method = 'echo')
	{
		global $boarddir;

		$funcName = 'ssi_todays' . ucfirst($type);

		require_once($boarddir . '/SSI.php');

		return function_exists($funcName) ? $funcName($output_method) : '';
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $txt, $scripturl;

		if ($type !== 'todays')
			return;

		$result = $this->getData($parameters['widget_type'], 'array');

		ob_start();

		if ($parameters['widget_type'] == 'calendar') {
			if (!empty($result['calendar_holidays']) || !empty($result['calendar_birthdays']) || !empty($result['calendar_events']))
				$this->getData($parameters['widget_type']);
			else
				echo $txt['lp_todays_addon_empty_list'];
		} elseif (!empty($result)) {
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
		</a>' . (!$member['is_last'] ? ', ' : '');
				}

				$hiddenContent = '';
				foreach ($hiddenItems as $member) {
					$hiddenContent .= '
		<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '">
			<span class="fix_rtl_names">' . $member['name'] . '</span>' . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '
		</a>' . (!$member['is_last'] ? ', ' : '');
				}

				// HTML5 spoiler
				if (!empty($hiddenContent))
					echo $txt['lp_todays_addon_and_more'], '
		<details>
			<summary>
				<span>', Helpers::getText(count($result) - $parameters['max_items'], $txt['lp_todays_addon_birthdays_set']), '</span>
			</summary>
			<div>', $hiddenContent, '</div>
		</details>';
			}
		} else {
			echo $txt['lp_todays_addon_empty_list'];
		}

		$content = ob_get_clean();
	}
}
