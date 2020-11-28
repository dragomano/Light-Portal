<?php

namespace Bugo\LightPortal\Addons\Todays;

/**
 * Todays
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Todays
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public $addon_icon = 'fas fa-calendar-day';

	/**
	 * What is displayed (birthdays|holidays|events|calendar)
	 *
	 * Что отображаем (birthdays|holidays|events|calendar)
	 *
	 * @var string
	 */
	private $type = 'calendar';

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['todays']['parameters']['widget_type'] = $this->type;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'todays')
			return;

		$parameters['widget_type'] = FILTER_SANITIZE_STRING;
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
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
			if (RC2_CLEAN) {
				$context['posting_fields']['widget_type']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['widget_type']
				);
			} else {
				$context['posting_fields']['widget_type']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['widget_type']
				);
			}
		}
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
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $txt;

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
			$this->getData($parameters['widget_type']);
		} else {
			echo $txt['lp_todays_addon_empty_list'];
		}

		$content = ob_get_clean();
	}
}
