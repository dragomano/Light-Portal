<?php

namespace Bugo\LightPortal\Addons\Todays;

use Bugo\LightPortal\Helpers;

/**
 * Todays
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Todays
{
	/**
	 * Что отображаем (birthdays|holidays|events|calendar)
	 *
	 * @var string
	 */
	private static $type = 'calendar';

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['todays'] = array(
			'parameters' => array(
				'widget_type' => static::$type
			)
		);
	}

	/**
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'todays')
			return;

		$args['parameters'] = array(
			'widget_type' => FILTER_SANITIZE_STRING
		);
	}

	/**
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
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
			'options' => array()
		);

		foreach ($txt['lp_todays_addon_type_set'] as $key => $value) {
			if (!defined('JQUERY_VERSION')) {
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
	 * Получаем список того, что запрашиваем
	 *
	 * @param string $type
	 * @param string $output_method
	 * @return string
	 */
	public static function getTodays($type, $output_method = 'echo')
	{
		global $boarddir;

		$funcName = 'ssi_todays' . ucfirst($type);

		require_once($boarddir . '/SSI.php');
		return function_exists($funcName) ? $funcName($output_method) : '';
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $txt;

		if ($type !== 'todays')
			return;

		ob_start();

		$result = self::getTodays($parameters['widget_type'], 'array');

		if ($parameters['widget_type'] == 'calendar') {
			if (!empty($result['calendar_holidays']) || !empty($result['calendar_birthdays']) || !empty($result['calendar_events']))
				self::getTodays($parameters['widget_type']);
			else
				echo $txt['lp_todays_addon_empty_list'];
		} elseif (!empty($result)) {
			self::getTodays($parameters['widget_type']);
		} else {
			echo $txt['lp_todays_addon_empty_list'];
		}

		$content = ob_get_clean();
	}
}
