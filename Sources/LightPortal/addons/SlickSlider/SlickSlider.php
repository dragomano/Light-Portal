<?php

namespace Bugo\LightPortal\Addons\SlickSlider;

use Bugo\LightPortal\Helpers;

/**
 * SlickSlider
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class SlickSlider
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public $addon_icon = 'fab fa-slideshare';

	/**
	 * Display arrows (true|false)
	 *
	 * Отображать стрелки «Вперёд и Назад»
	 *
	 * @var bool
	 */
	private $show_arrows = true;

	/**
	 * Display slider dots
	 *
	 * Отображать точки под слайдером
	 *
	 * @var bool
	 */
	private $show_dots = true;

	/**
	 * Adapts slider height to the current slide
	 *
	 * Адаптирует высоту слайдера к текущему слайду
	 *
	 * @var bool
	 */
	private $adaptive_height = true;

	/**
	 * The number of slides to show at a time
	 *
	 * Количество одновременно отображаемых слайдов
	 *
	 * @var int
	 */
	private $slides_to_show = 3;

	/**
	 * Number of slides to scroll at a time
	 *
	 * Количество слайдов, прокручиваемых за раз
	 *
	 * @var int
	 */
	private $slides_to_scroll = 1;

	/**
	 * The slider autoplay
	 *
	 * Автозапуск слайдера
	 *
	 * @var bool
	 */
	private $autoplay = true;

	/**
	 * Auto play change interval
	 *
	 * Интервал переключения между слайдами
	 *
	 * @var int
	 */
	private $autoplay_speed = 800;

	/**
	 * Transition speed
	 *
	 * Скорость перехода
	 *
	 * @var int
	 */
	private $speed = 1000;

	/**
	 * Image list for slider
	 *
	 * Список изображений для слайдера
	 *
	 * @var string
	 */
	private $images = '';

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
		$options['slick_slider']['parameters']['show_arrows']      = $this->show_arrows;
		$options['slick_slider']['parameters']['show_dots']        = $this->show_dots;
		$options['slick_slider']['parameters']['adaptive_height']  = $this->adaptive_height;
		$options['slick_slider']['parameters']['slides_to_show']   = $this->slides_to_show;
		$options['slick_slider']['parameters']['slides_to_scroll'] = $this->slides_to_scroll;
		$options['slick_slider']['parameters']['autoplay']         = $this->autoplay;
		$options['slick_slider']['parameters']['autoplay_speed']   = $this->autoplay_speed;
		$options['slick_slider']['parameters']['speed']            = $this->speed;
		$options['slick_slider']['parameters']['images']           = $this->images;
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
		if ($type !== 'slick_slider')
			return;

		$parameters['show_arrows']      = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_dots']        = FILTER_VALIDATE_BOOLEAN;
		$parameters['adaptive_height']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['slides_to_show']   = FILTER_VALIDATE_INT;
		$parameters['slides_to_scroll'] = FILTER_VALIDATE_INT;
		$parameters['autoplay']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['autoplay_speed']   = FILTER_VALIDATE_INT;
		$parameters['speed']            = FILTER_VALIDATE_INT;
		$parameters['images']           = FILTER_SANITIZE_STRING;
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

		if ($context['lp_block']['type'] !== 'slick_slider')
			return;

		$context['posting_fields']['show_arrows']['label']['text'] = $txt['lp_slick_slider_addon_show_arrows'];
		$context['posting_fields']['show_arrows']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_arrows',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_arrows'])
			)
		);

		$context['posting_fields']['show_dots']['label']['text'] = $txt['lp_slick_slider_addon_show_dots'];
		$context['posting_fields']['show_dots']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_dots',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_dots'])
			)
		);

		$context['posting_fields']['adaptive_height']['label']['text'] = $txt['lp_slick_slider_addon_adaptive_height'];
		$context['posting_fields']['adaptive_height']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'adaptive_height',
				'checked' => !empty($context['lp_block']['options']['parameters']['adaptive_height'])
			)
		);

		$context['posting_fields']['slides_to_show']['label']['text'] = $txt['lp_slick_slider_addon_slides_to_show'];
		$context['posting_fields']['slides_to_show']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'slides_to_show',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['slides_to_show']
			)
		);

		$context['posting_fields']['slides_to_scroll']['label']['text'] = $txt['lp_slick_slider_addon_slides_to_scroll'];
		$context['posting_fields']['slides_to_scroll']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'slides_to_scroll',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['slides_to_scroll']
			)
		);

		$context['posting_fields']['autoplay']['label']['text'] = $txt['lp_slick_slider_addon_autoplay'];
		$context['posting_fields']['autoplay']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'autoplay',
				'checked' => !empty($context['lp_block']['options']['parameters']['autoplay'])
			)
		);

		$context['posting_fields']['speed']['label']['text'] = $txt['lp_slick_slider_addon_speed'];
		$context['posting_fields']['speed']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'speed',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['speed']
			)
		);

		$context['posting_fields']['autoplay_speed']['label']['text'] = $txt['lp_slick_slider_addon_autoplay_speed'];
		$context['posting_fields']['autoplay_speed']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'autoplay_speed',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['autoplay_speed']
			)
		);

		$context['posting_fields']['images']['label']['text'] = $txt['lp_slick_slider_addon_images'];
		$context['posting_fields']['images']['input'] = array(
			'type' => 'textarea',
			'after' => $txt['lp_slick_slider_addon_images_subtext'],
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
	public function getHtml($block_id, $parameters)
	{
		if (empty($parameters['images']))
			return '';

		$html = '
		<div id="slick_slider' . $block_id . '">';

		$i = 0;

		$images = explode(PHP_EOL, $parameters['images']);
		foreach ($images as $image) {
			$html .= '
			<div class="slider__item' . ($i++ % 2 == 0 ? ' filter' : '') . '">
				<img data-lazy="' . $image . '" alt="">
			</div>';
		}

		$html .= '
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
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		if ($type !== 'slick_slider')
			return;

		$slick_slider_html = Helpers::cache('slick_slider_addon_b' . $block_id, 'getHtml', __CLASS__, $cache_time, $block_id, $parameters);

		if (!empty($slick_slider_html)) {
			loadCSSFile('https://cdn.jsdelivr.net/npm/slick-carousel@1/slick/slick.css', array('external' => true));
			loadCSSFile('https://cdn.jsdelivr.net/npm/slick-carousel@1/slick/slick-theme.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/slick-carousel@1/slick/slick.min.js', array('external' => true));
			addInlineJavaScript('
			$("#slick_slider' . $block_id . '").slick({
				arrows: ' . (!empty($parameters['show_arrows']) ? 'true' : 'false') . ',
				dots: ' . (!empty($parameters['show_dots']) ? 'true' : 'false') . ',
				adaptiveHeight: ' . (!empty($parameters['adaptive_height']) ? 'true' : 'false') . ',
				slidesToShow: ' . (!empty($parameters['slides_to_show']) ? $parameters['slides_to_show'] : $this->slides_to_show) . ',
				slidesToScroll: ' . (!empty($parameters['slides_to_scroll']) ? $parameters['slides_to_scroll'] : $this->slides_to_scroll) . ',
				autoplay: ' . (!empty($parameters['autoplay']) ? 'true' : 'false') . ',
				autoplaySpeed: ' . (!empty($parameters['autoplay_speed']) ? $parameters['autoplay_speed'] : $this->autoplay_speed) . ',
				speed: ' . (!empty($parameters['speed']) ? $parameters['speed'] : $this->speed) . ',
				responsive: [
					{
						breakpoint: 768,
						settings: {
							slidesToShow: 2
						}
					},
					{
						breakpoint: 550,
						settings: {
							slidesToShow: 1
						}
					}
				]
			});', true);

			ob_start();
			echo $slick_slider_html;
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
	public function credits(&$links)
	{
		$links[] = array(
			'title' => 'slick',
			'link' => 'https://github.com/kenwheeler/slick/',
			'author' => 'Ken Wheeler',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/kenwheeler/slick/blob/master/LICENSE'
			)
		);
	}
}
