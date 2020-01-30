<?php

namespace Bugo\LightPortal\Addons\YandexTranslate;

/**
 * YandexTranslate
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class YandexTranslate
{
	/**
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Цветовая тема виджета (light|dark)
	 *
	 * @var string
	 */
	private static $widget_theme = 'light';

	/**
	 * Автоматический перевод (если выключен, то требуется нажатие на кнопку «Перевести»)
	 *
	 * @var bool
	 */
	private static $auto_mode = false;

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['yandex_translate'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'widget_theme' => static::$widget_theme,
				'auto_mode'    => static::$auto_mode
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

		if ($context['current_block']['type'] !== 'yandex_translate')
			return;

		$args['parameters'] = array(
			'widget_theme' => FILTER_SANITIZE_STRING,
			'auto_mode'    => FILTER_VALIDATE_BOOLEAN
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

		if ($context['lp_block']['type'] !== 'yandex_translate')
			return;

		$context['posting_fields']['widget_theme']['label']['text'] = $txt['lp_yandex_translate_addon_widget_theme'];
		$context['posting_fields']['widget_theme']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'widget_theme'
			)
		);

		if (!defined('JQUERY_VERSION')) {
			$context['posting_fields']['widget_theme']['input']['options'] = array(
				'light' => array(
					'attributes' => array(
						'value'    => 'light',
						'selected' => 'light' == $context['lp_block']['options']['parameters']['widget_theme']
					)
				),
				'dark' => array(
					'attributes' => array(
						'value'    => 'dark',
						'selected' => 'dark' == $context['lp_block']['options']['parameters']['widget_theme']
					)
				)
			);
		} else {
			$context['posting_fields']['widget_theme']['input']['options'] = array(
				'light' => array(
					'value'    => 'light',
					'selected' => 'light' == $context['lp_block']['options']['parameters']['widget_theme']
				),
				'dark' => array(
					'value'    => 'dark',
					'selected' => 'dark' == $context['lp_block']['options']['parameters']['widget_theme']
				)
			);
		}

		$context['posting_fields']['auto_mode']['label']['text'] = $txt['lp_yandex_translate_addon_auto_mode'];
		$context['posting_fields']['auto_mode']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'auto_mode',
				'checked' => !empty($context['lp_block']['options']['parameters']['auto_mode'])
			)
		);
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
		global $language;

		if ($type !== 'yandex_translate')
			return;

		ob_start();

		echo '
		<div id="ytWidget" class="centertext noup"></div>
		<script src="https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget&amp;pageLang=', substr($language, 0, 2), '&amp;widgetTheme=', $parameters['widget_theme'], '&amp;autoMode=', (bool) $parameters['auto_mode'], '"></script>';

		$content = ob_get_clean();
	}
}
