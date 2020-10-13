<?php

namespace Bugo\LightPortal\Addons\FlipsterCarousel;

use Bugo\LightPortal\Helpers;

/**
 * FlipsterCarousel
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

class FlipsterCarousel
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'far fa-images';

	/**
	 * The slider autoplay, in ms
	 *
	 * Автозапуск слайдера, в мс
	 *
	 * @var int
	 */
	private static $autoplay = 0;

	/**
	 * The slider style
	 *
	 * Стиль слайдера
	 *
	 * @var string
	 */
	private static $style = 'coverflow';

	/**
	 * Display the navigation (true|false)
	 *
	 * Отображать навигацию (заголовки и категории)
	 *
	 * @var bool
	 */
	private static $show_nav = true;

	/**
	 * Display Previous/Next buttons
	 *
	 * Отображать кнопки «Предыдущая» и «Следующая»
	 *
	 * @var bool
	 */
	private static $show_buttons = false;

	/**
	 * Image list for slider
	 *
	 * Список изображений для слайдера
	 *
	 * @var string
	 */
	private static $images = '';

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
		$options['flipster_carousel']['parameters']['autoplay']     = static::$autoplay;
		$options['flipster_carousel']['parameters']['style']        = static::$style;
		$options['flipster_carousel']['parameters']['show_nav']     = static::$show_nav;
		$options['flipster_carousel']['parameters']['show_buttons'] = static::$show_buttons;
		$options['flipster_carousel']['parameters']['images']       = static::$images;
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
	public static function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'flipster_carousel')
			return;

		$parameters['autoplay']     = FILTER_VALIDATE_INT;
		$parameters['style']        = FILTER_SANITIZE_STRING;
		$parameters['show_nav']     = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_buttons'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['images']       = FILTER_SANITIZE_STRING;
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

		if ($context['lp_block']['type'] !== 'flipster_carousel')
			return;

		$context['posting_fields']['autoplay']['label']['text'] = $txt['lp_flipster_carousel_addon_autoplay'];
		$context['posting_fields']['autoplay']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'autoplay',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['autoplay']
			)
		);

		$context['posting_fields']['style']['label']['text'] = $txt['lp_flipster_carousel_addon_style'];
		$context['posting_fields']['style']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'style'
			),
			'options' => array()
		);

		foreach ($txt['lp_flipster_carousel_addon_style_set'] as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['style']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['style']
				);
			} else {
				$context['posting_fields']['style']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['style']
				);
			}
		}

		$context['posting_fields']['show_nav']['label']['text'] = $txt['lp_flipster_carousel_addon_show_nav'];
		$context['posting_fields']['show_nav']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_nav',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_nav'])
			)
		);

		$context['posting_fields']['show_buttons']['label']['text'] = $txt['lp_flipster_carousel_addon_show_buttons'];
		$context['posting_fields']['show_buttons']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_buttons',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_buttons'])
			)
		);

		$context['posting_fields']['images']['label']['text'] = $txt['lp_flipster_carousel_addon_images'];
		$context['posting_fields']['images']['input'] = array(
			'type' => 'textarea',
			'after' => $txt['lp_flipster_carousel_addon_images_subtext'],
			'attributes' => array(
				'id'       => 'images',
				'value'    => $context['lp_block']['options']['parameters']['images'],
				'required' => true
			),
			'tab' => 'content'
		);
	}

	/**
	 * Get the block html code
	 *
	 * Получаем html-код блока
	 *
	 * @param int $block_id
	 * @param array $parameters
	 * @return string
	 */
	public static function getHtml($block_id, $parameters)
	{
		if (empty($parameters['images']))
			return '';

		$html = '
		<div id="flipster_carousel_coverflow' . $block_id . '">
			<ul>';

		$images = explode(PHP_EOL, $parameters['images']);
		foreach ($images as $data) {
			$image = explode("|", $data);

			$html .= '
				<li' . (!empty($image[1]) ? (' data-flip-title="' . $image[1] . '"') : '') . (!empty($image[2]) ? (' data-flip-category="' . $image[2] . '"') : '') . '>
					<img src="' . $image[0] . '" alt="' . (!empty($image[1]) ? $image[1] : '') . '">
				</li>';
		}

		$html .= '
			</ul>
		</div>';

		return $html;
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
		global $txt;

		if ($type !== 'flipster_carousel')
			return;

		$flipster_html = Helpers::cache('flipster_carousel_addon_b' . $block_id, 'getHtml', __CLASS__, $cache_time, $block_id, $parameters);

		if (!empty($flipster_html)) {
			loadCSSFile('https://cdn.jsdelivr.net/npm/jquery.flipster@1/dist/jquery.flipster.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/jquery.flipster@1/dist/jquery.flipster.min.js', array('external' => true));
			addInlineJavaScript('
			$("#flipster_carousel_coverflow' . $block_id . '").flipster({
				start: "center",
				fadeIn: 400,
				loop: true,
				autoplay: ' . (!empty($parameters['autoplay']) ? $parameters['autoplay'] : 'false') . ',
				pauseOnHover: true,
				style: "' . $parameters['style'] . '",
				spacing: -0.6,
				click: true,
				keyboard: true,
				scrollwheel: true,
				touch: true,
				nav: ' . ($parameters['show_nav'] ? 'true' : 'false') . ',
				buttons: ' . ($parameters['show_buttons'] ? 'true' : 'false') . ',
				buttonPrev: "' . $txt['lp_flipster_carousel_addon_prev'] . '",
				buttonNext: "' . $txt['lp_flipster_carousel_addon_next'] . '",
			});', true);

			ob_start();
			echo $flipster_html;
			$content = ob_get_clean();
		}
	}

	/**
	 * Adding the addon copyright
	 *
	 * Добавляем копирайты плагина
	 *
	 * @param array $links
	 * @return void
	 */
	public static function credits(&$links)
	{
		$links[] = array(
			'title' => 'jQuery.Flipster',
			'link' => 'https://github.com/drien/jquery-flipster/',
			'author' => 'Adrien Delessert',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/drien/jquery-flipster/blob/master/LICENSE'
			)
		);
	}
}
