<?php

namespace Bugo\LightPortal\Addons\TinySlider;

use Bugo\LightPortal\Helpers;

/**
 * TinySlider
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

class TinySlider
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-images';

	/**
	 * @var string
	 */
	public $axis = 'horizontal';

	/**
	 * @var int
	 */
	private $items = 1;

	/**
	 * @var int
	 */
	private $gutter = 0;

	/**
	 * @var int
	 */
	private $edge_padding = 0;

	/**
	 * @var bool
	 */
	private $controls = true;

	/**
	 * @var bool
	 */
	private $nav = true;

	/**
	 * @var bool
	 */
	private $nav_as_thumbnails = false;

	/**
	 * @var bool
	 */
	private $arrow_keys = false;

	/**
	 * @var int
	 */
	private $fixed_width = 0;

	/**
	 * @var bool
	 */
	private $auto_width = false;

	/**
	 * @var bool
	 */
	private $auto_height = false;

	/**
	 * @var int
	 */
	private $slide_by = 1;

	/**
	 * @var int
	 */
	private $speed = 300;

	/**
	 * @var bool
	 */
	private $autoplay = true;

	/**
	 * @var int
	 */
	private $autoplay_timeout = 5000;

	/**
	 * @var string
	 */
	private $autoplay_direction = 'forward';

	/**
	 * @var bool
	 */
	private $loop = true;

	/**
	 * @var bool
	 */
	private $rewind = false;

	/**
	 * @var bool
	 */
	private $lazyload = false;

	/**
	 * @var bool
	 */
	private $mouse_drag = false;

	/**
	 * @var string
	 */
	private $images = '';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['tiny_slider']['parameters']['axis']               = $this->axis;
		$options['tiny_slider']['parameters']['items']              = $this->items;
		$options['tiny_slider']['parameters']['gutter']             = $this->gutter;
		$options['tiny_slider']['parameters']['edge_padding']       = $this->edge_padding;
		$options['tiny_slider']['parameters']['controls']           = $this->controls;
		$options['tiny_slider']['parameters']['nav']                = $this->nav;
		$options['tiny_slider']['parameters']['nav_as_thumbnails']  = $this->nav_as_thumbnails;
		$options['tiny_slider']['parameters']['arrow_keys']         = $this->arrow_keys;
		$options['tiny_slider']['parameters']['fixed_width']        = $this->fixed_width;
		$options['tiny_slider']['parameters']['auto_width']         = $this->auto_width;
		$options['tiny_slider']['parameters']['auto_height']        = $this->auto_height;
		$options['tiny_slider']['parameters']['slide_by']           = $this->slide_by;
		$options['tiny_slider']['parameters']['speed']              = $this->speed;
		$options['tiny_slider']['parameters']['autoplay']           = $this->autoplay;
		$options['tiny_slider']['parameters']['autoplay_timeout']   = $this->autoplay_timeout;
		$options['tiny_slider']['parameters']['autoplay_direction'] = $this->autoplay_direction;
		$options['tiny_slider']['parameters']['loop']               = $this->loop;
		$options['tiny_slider']['parameters']['rewind']             = $this->rewind;
		$options['tiny_slider']['parameters']['lazyload']           = $this->lazyload;
		$options['tiny_slider']['parameters']['mouse_drag']         = $this->mouse_drag;
		$options['tiny_slider']['parameters']['images']             = $this->images;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'tiny_slider')
			return;

		$parameters['axis']               = FILTER_SANITIZE_STRING;
		$parameters['items']              = FILTER_VALIDATE_INT;
		$parameters['gutter']             = FILTER_VALIDATE_INT;
		$parameters['edge_padding']       = FILTER_VALIDATE_INT;
		$parameters['controls']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['nav']                = FILTER_VALIDATE_BOOLEAN;
		$parameters['nav_as_thumbnails']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['arrow_keys']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['fixed_width']        = FILTER_VALIDATE_INT;
		$parameters['auto_width']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['auto_height']        = FILTER_VALIDATE_BOOLEAN;
		$parameters['slide_by']           = FILTER_VALIDATE_INT;
		$parameters['speed']              = FILTER_VALIDATE_INT;
		$parameters['autoplay']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['autoplay_timeout']   = FILTER_SANITIZE_STRING;
		$parameters['autoplay_direction'] = FILTER_SANITIZE_STRING;
		$parameters['loop']               = FILTER_VALIDATE_BOOLEAN;
		$parameters['rewind']             = FILTER_VALIDATE_BOOLEAN;
		$parameters['lazyload']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['mouse_drag']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['images']             = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'tiny_slider')
			return;

		$context['posting_fields']['axis']['label']['text'] = $txt['lp_tiny_slider_addon_axis'];
		$context['posting_fields']['axis']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'axis'
			),
			'options' => array()
		);

		$axis_directions = array_combine(array('horizontal', 'vertical'), $txt['lp_panel_direction_set']);

		foreach ($axis_directions as $key => $value) {
			$context['posting_fields']['axis']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['axis']
			);
		}

		$context['posting_fields']['items']['label']['text'] = $txt['lp_tiny_slider_addon_items'];
		$context['posting_fields']['items']['input'] = array(
			'after' => $txt['lp_tiny_slider_addon_items_subtext'],
			'type' => 'number',
			'attributes' => array(
				'id'    => 'items',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['items']
			)
		);

		$context['posting_fields']['gutter']['label']['text'] = $txt['lp_tiny_slider_addon_gutter'];
		$context['posting_fields']['gutter']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'gutter',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['gutter']
			)
		);

		$context['posting_fields']['edge_padding']['label']['text'] = $txt['lp_tiny_slider_addon_edge_padding'];
		$context['posting_fields']['edge_padding']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'edge_padding',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['edge_padding']
			)
		);

		$context['posting_fields']['controls']['label']['text'] = $txt['lp_tiny_slider_addon_controls'];
		$context['posting_fields']['controls']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'controls',
				'checked' => !empty($context['lp_block']['options']['parameters']['controls'])
			)
		);

		$context['posting_fields']['nav']['label']['text'] = $txt['lp_tiny_slider_addon_nav'];
		$context['posting_fields']['nav']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'nav',
				'checked' => !empty($context['lp_block']['options']['parameters']['nav'])
			)
		);

		$context['posting_fields']['nav_as_thumbnails']['label']['text'] = $txt['lp_tiny_slider_addon_nav_as_thumbnails'];
		$context['posting_fields']['nav_as_thumbnails']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'nav_as_thumbnails',
				'checked' => !empty($context['lp_block']['options']['parameters']['nav_as_thumbnails'])
			)
		);

		$context['posting_fields']['arrow_keys']['label']['text'] = $txt['lp_tiny_slider_addon_arrow_keys'];
		$context['posting_fields']['arrow_keys']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'arrow_keys',
				'checked' => !empty($context['lp_block']['options']['parameters']['arrow_keys'])
			)
		);

		$context['posting_fields']['fixed_width']['label']['text'] = $txt['lp_tiny_slider_addon_fixed_width'];
		$context['posting_fields']['fixed_width']['input'] = array(
			'after' => $txt['zero_for_no_limit'],
			'type' => 'number',
			'attributes' => array(
				'id'    => 'fixed_width',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['fixed_width']
			)
		);

		$context['posting_fields']['auto_width']['label']['text'] = $txt['lp_tiny_slider_addon_auto_width'];
		$context['posting_fields']['auto_width']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'auto_width',
				'checked' => !empty($context['lp_block']['options']['parameters']['auto_width'])
			)
		);

		$context['posting_fields']['auto_height']['label']['text'] = $txt['lp_tiny_slider_addon_auto_height'];
		$context['posting_fields']['auto_height']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'auto_height',
				'checked' => !empty($context['lp_block']['options']['parameters']['auto_height'])
			)
		);

		$context['posting_fields']['slide_by']['label']['text'] = $txt['lp_tiny_slider_addon_slide_by'];
		$context['posting_fields']['slide_by']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'slide_by',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['slide_by']
			)
		);

		$context['posting_fields']['speed']['label']['text'] = $txt['lp_tiny_slider_addon_speed'];
		$context['posting_fields']['speed']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'speed',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['speed']
			)
		);

		$context['posting_fields']['autoplay']['label']['text'] = $txt['lp_tiny_slider_addon_autoplay'];
		$context['posting_fields']['autoplay']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'autoplay',
				'checked' => !empty($context['lp_block']['options']['parameters']['autoplay'])
			)
		);

		$context['posting_fields']['autoplay_timeout']['label']['text'] = $txt['lp_tiny_slider_addon_autoplay_timeout'];
		$context['posting_fields']['autoplay_timeout']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'autoplay_timeout',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['autoplay_timeout']
			)
		);

		$context['posting_fields']['autoplay_direction']['label']['text'] = $txt['lp_tiny_slider_addon_autoplay_direction'];
		$context['posting_fields']['autoplay_direction']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'autoplay_direction'
			),
			'options' => array()
		);

		$autoplay_directions = array_combine(array('forward', 'backward'), $txt['lp_tiny_slider_addon_autoplay_direction_set']);

		foreach ($autoplay_directions as $key => $value) {
			$context['posting_fields']['autoplay_direction']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['autoplay_direction']
			);
		}

		$context['posting_fields']['loop']['label']['text'] = $txt['lp_tiny_slider_addon_loop'];
		$context['posting_fields']['loop']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'loop',
				'checked' => !empty($context['lp_block']['options']['parameters']['loop'])
			)
		);

		$context['posting_fields']['rewind']['label']['text'] = $txt['lp_tiny_slider_addon_rewind'];
		$context['posting_fields']['rewind']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'rewind',
				'checked' => !empty($context['lp_block']['options']['parameters']['rewind'])
			)
		);

		$context['posting_fields']['lazyload']['label']['text'] = $txt['lp_tiny_slider_addon_lazyload'];
		$context['posting_fields']['lazyload']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'lazyload',
				'checked' => !empty($context['lp_block']['options']['parameters']['lazyload'])
			)
		);

		$context['posting_fields']['mouse_drag']['label']['text'] = $txt['lp_tiny_slider_addon_mouse_drag'];
		$context['posting_fields']['mouse_drag']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'mouse_drag',
				'checked' => !empty($context['lp_block']['options']['parameters']['mouse_drag'])
			)
		);

		$context['posting_fields']['images']['label']['text'] = $txt['lp_tiny_slider_addon_images'];
		$context['posting_fields']['images']['input'] = array(
			'type' => 'textarea',
			'after' => $txt['lp_tiny_slider_addon_images_subtext'],
			'attributes' => array(
				'id'       => 'images',
				'value'    => $context['lp_block']['options']['parameters']['images'],
				'required' => true
			),
			'tab' => 'content'
		);
	}

	/**
	 * @param int $block_id
	 * @param array $parameters
	 * @return string
	 */
	public function getHtml($block_id, $parameters)
	{
		global $txt;

		if (empty($parameters['images']))
			return '';

		$html = '
		<div id="tiny_slider' . $block_id . '">';

		$images = explode(PHP_EOL, $parameters['images']);
		foreach ($images as $image) {
			$image = explode("|", $image);

			$html .= '
			<div class="item">
				<img ' . (!empty($parameters['lazyload']) ? 'class="tns-lazy-img" data-' : '') . 'src="' . $image[0] . '" alt="' . (!empty($image[1]) ? $image['1'] : '') . '"' . (!empty($parameters['fixed_width']) ? ' width="' . $parameters['fixed_width'] . '"' : '') . '>';

			if (!empty($image[1])) {
				$html .= '
				<p>' . $image['1'] . '</p>';
			}

			$html .= '
			</div>';
		}

		$html .= '
		</div>
		<div class="customize-tools">';

		if (!empty($parameters['nav']) && !empty($parameters['nav_as_thumbnails'])) {
			$html .= '
			<ul class="thumbnails customize-thumbnails"' . (!empty($parameters['controls']) ? ' style="margin-bottom: -30px"' : '') . '>';

			foreach ($images as $image) {
				$image = explode("|", $image);

				$html .= '
				<li><img src="' . $image[0] . '" alt="' . (!empty($image[1]) ? $image['1'] : '') . '"></li>';
			}

			$html .= '
			</ul>';
		}

		if (!empty($parameters['controls'])) {
			$buttons = array_combine(array('prev', 'next'), $txt['lp_tiny_slider_addon_controls_buttons']);

			$html .= '
			<ul class="controls customize-controls">
				<li class="prev">
					<span class="button floatleft"><i class="fas fa-arrow-left"></i> ' . $buttons['prev'] . '</span>
				</li>
				<li class="next">
					<span class="button floatright">' . $buttons['next'] . ' <i class="fas fa-arrow-right"></i></span>
				</li>
			</ul>';
		}

		$html .= '
		</div>';

		return $html;
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
		global $txt;

		if ($type !== 'tiny_slider')
			return;

		$tiny_slider_html = Helpers::cache('tiny_slider_addon_b' . $block_id, 'getHtml', __CLASS__, $cache_time, $block_id, $parameters);

		if (!empty($tiny_slider_html)) {
			loadCSSFile('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.min.js', array('external' => true));
			addInlineJavaScript('
			let slider' . $block_id . ' = tns({
				container: "#tiny_slider' . $block_id . '",
				axis: "' . (!empty($parameters['axis']) ? $parameters['axis'] : $this->axis) . '",
				items: ' . (!empty($parameters['items']) ? $parameters['items'] : $this->items) . ',
				gutter: ' . (!empty($parameters['gutter']) ? $parameters['gutter'] : $this->gutter) . ',
				edgePadding: ' . (!empty($parameters['edge_padding']) ? $parameters['edge_padding'] : $this->edge_padding) . ',
				fixedWidth: ' . (!empty($parameters['fixed_width']) ? $parameters['fixed_width'] : $this->fixed_width) . ',
				autoWidth: ' . (!empty($parameters['auto_width']) ? 'true' : 'false') . ',
				autoHeight: ' . (!empty($parameters['auto_height']) ? 'true': 'false') . ',
				slideBy: ' . (!empty($parameters['slide_by']) ? $parameters['slide_by'] : $this->slide_by) . ',
				controls: ' . (!empty($parameters['controls']) ? 'true' : 'false') . ',
				controlsContainer: ".customize-controls",
				nav: ' . (!empty($parameters['nav']) ? 'true' : 'false') . ',
				navPosition: "bottom",' . (!empty($parameters['nav']) && !empty($parameters['nav_as_thumbnails']) ? '
				navContainer: ".customize-thumbnails",' : '') . '
				navAsThumbnails: ' . (!empty($parameters['nav_as_thumbnails']) ? 'true' : 'false') . ',
				arrowKeys: ' . (!empty($parameters['arrow_keys']) ? 'true' : 'false') . ',
				speed: ' . (!empty($parameters['speed']) ? $parameters['speed'] : $this->speed) . ',
				autoplay: ' . (!empty($parameters['autoplay']) ? 'true' : 'false') . ',
				autoplayTimeout: ' . (!empty($parameters['autoplay_timeout']) ? $parameters['autoplay_timeout'] : $this->autoplay_timeout) . ',
				autoplayDirection: "' . (!empty($parameters['autoplay_direction']) ? $parameters['autoplay_direction'] : $this->autoplay_direction) . '",
				autoplayHoverPause: true,
				autoplayButtonOutput: false,
				loop: ' . (!empty($parameters['loop']) ? 'true' : 'false') . ',
				rewind: ' . (!empty($parameters['rewind']) ? 'true' : 'false') . ',
				responsive: {
					640: {
						edgePadding: 20,
						gutter: 20,
						items: 2
					},
					700: {
						gutter: 30
					},
					900: {
						items: 3
					}
				},
				lazyload: ' . (!empty($parameters['lazyload']) ? 'true' : 'false') . ',
				mouseDrag: ' . (!empty($parameters['mouse_drag']) ? 'true' : 'false') . ',
				freezable: false
			});', true);

			ob_start();
			echo $tiny_slider_html;
			$content = ob_get_clean();
		}
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
	{
		$links[] = array(
			'title' => 'Tiny Slider 2',
			'link' => 'https://github.com/ganlanyuan/tiny-slider',
			'author' => 'William Lin',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/ganlanyuan/tiny-slider/blob/master/LICENSE'
			)
		);
	}
}
