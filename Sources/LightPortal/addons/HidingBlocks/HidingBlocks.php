<?php

namespace Bugo\LightPortal\Addons\HidingBlocks;

use Bugo\LightPortal\Helpers;

/**
 * HidingBlocks
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

class HidingBlocks
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public static $addon_type = 'other';

	/**
	 * Supported screen sizes
	 *
	 * Поддерживаемые размеры экранов
	 *
	 * @var array
	 */
	public static $classes = ['xs', 'sm', 'md', 'lg', 'xl'];

	/**
	 * Hidden classes that enabled by default
	 *
	 * Включенные скрытые классы по умолчанию
	 *
	 * @var array
	 */
	private static $hidden_breakpoints = [];

	/**
	 * Fill additional block classes
	 *
	 * Заполняем дополнительные классы блока
	 *
	 * @return void
	 */
	public static function init()
	{
		global $context;

		if (empty($context['lp_active_blocks']))
			return;

		foreach ($context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['hidden_breakpoints']))
				continue;

			$breakpoints = array_flip(explode(',', $block['parameters']['hidden_breakpoints']));
			foreach (self::$classes as $class) {
				if (array_key_exists($class, $breakpoints)) {
					if (empty($context['lp_active_blocks'][$id]['custom_class']))
						$context['lp_active_blocks'][$id]['custom_class'] = '';

					$context['lp_active_blocks'][$id]['custom_class'] .= ' hidden-' . $class;
				}
			}
		}
	}

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
		global $context;

		$options[$context['current_block']['type']]['parameters']['hidden_breakpoints'] = static::$hidden_breakpoints;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @return void
	 */
	public static function validateBlockData(&$parameters)
	{
		$parameters['hidden_breakpoints'] = array(
			'name'   => 'hidden_breakpoints',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if (isset($context['lp_block']['options']['parameters']['hidden_breakpoints'])) {
			$context['lp_block']['options']['parameters']['hidden_breakpoints'] = is_array($context['lp_block']['options']['parameters']['hidden_breakpoints']) ? $context['lp_block']['options']['parameters']['hidden_breakpoints'] : explode(',', $context['lp_block']['options']['parameters']['hidden_breakpoints']);
		} else
			$context['lp_block']['options']['parameters']['hidden_breakpoints'] = Helpers::post('hidden_breakpoints', []);

		$context['posting_fields']['hidden_breakpoints']['label']['text'] = $txt['lp_hiding_blocks_addon_hidden_breakpoints'];
		$context['posting_fields']['hidden_breakpoints']['input'] = array(
			'type' => 'select',
			'after' => $txt['lp_hiding_blocks_addon_hidden_breakpoints_subtext'],
			'attributes' => array(
				'id'       => 'hidden_breakpoints',
				'name'     => 'hidden_breakpoints[]',
				'multiple' => true,
				'style'    => 'height: 90px'
			),
			'options' => array()
		);

		foreach ($txt['lp_hiding_blocks_addon_hidden_breakpoints_set'] as $size => $label) {
			if (RC2_CLEAN) {
				$context['posting_fields']['hidden_breakpoints']['input']['options'][$label]['attributes'] = array(
					'value'    => $size,
					'selected' => in_array($size, $context['lp_block']['options']['parameters']['hidden_breakpoints'])
				);
			} else {
				$context['posting_fields']['hidden_breakpoints']['input']['options'][$label] = array(
					'value'    => $size,
					'selected' => in_array($size, $context['lp_block']['options']['parameters']['hidden_breakpoints'])
				);
			}
		}
	}
}
