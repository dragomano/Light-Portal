<?php

namespace Bugo\LightPortal\Addons\Translator;

/**
 * Translator
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Translator
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-language';

	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Used translation engine (google|yandex)
	 *
	 * Используемый движок для перевода (google|yandex)
	 *
	 * @var string
	 */
	private static $engine = 'google';

	/**
	 * The widget color theme (light|dark)
	 *
	 * Цветовая тема виджета (light|dark)
	 *
	 * @var string
	 */
	private static $widget_theme = 'light';

	/**
	 * Automatic translation (true|false)
	 *
	 * Автоматический перевод (если выключен, то требуется нажатие на кнопку «Перевести»)
	 *
	 * @var bool
	 */
	private static $auto_mode = false;

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
		$options['translator']['no_content_class'] = static::$no_content_class;

		$options['translator']['parameters']['engine']       = static::$engine;
		$options['translator']['parameters']['widget_theme'] = static::$widget_theme;
		$options['translator']['parameters']['auto_mode']    = static::$auto_mode;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'translator')
			return;

		$args['parameters']['engine']       = FILTER_SANITIZE_STRING;
		$args['parameters']['widget_theme'] = FILTER_SANITIZE_STRING;
		$args['parameters']['auto_mode']    = FILTER_VALIDATE_BOOLEAN;
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

		if ($context['lp_block']['type'] !== 'translator')
			return;

		$context['posting_fields']['engine']['label']['text'] = $txt['lp_translator_addon_engine'];
		$context['posting_fields']['engine']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'engine'
			)
		);

		foreach ($txt['lp_translator_addon_engine_set'] as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['engine']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['engine']
				);
			} else {
				$context['posting_fields']['engine']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['engine']
				);
			}
		}

		if ($context['lp_block']['options']['parameters']['engine'] == 'google')
			return;

		$context['posting_fields']['widget_theme']['label']['text'] = $txt['lp_translator_addon_widget_theme'];
		$context['posting_fields']['widget_theme']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'widget_theme'
			)
		);

		if (RC2_CLEAN) {
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

		$context['posting_fields']['auto_mode']['label']['text'] = $txt['lp_translator_addon_auto_mode'];
		$context['posting_fields']['auto_mode']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'auto_mode',
				'checked' => !empty($context['lp_block']['options']['parameters']['auto_mode'])
			)
		);
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
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $language;

		if ($type !== 'translator')
			return;

		ob_start();

		if ($parameters['engine'] == 'yandex') {
			echo '
		<div id="ytWidget', $block_id, '" class="centertext noup"></div>
		<script src="https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget', $block_id, '&amp;pageLang=', substr($language, 0, 2), '&amp;widgetTheme=', $parameters['widget_theme'], '&amp;autoMode=', (bool) $parameters['auto_mode'], '"></script>';
		} else {
			echo '
		<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		<div class="centertext noup">
			<div id="google_translate_element', $block_id, '"></div>
			<script>
				function googleTranslateElementInit() {
					new google.translate.TranslateElement({
						pageLanguage: "', substr($language, 0, 2), '"
					}, "google_translate_element', $block_id, '");
				}
			</script>
		</div>';
		}

		$content = ob_get_clean();
	}
}
