<?php

/**
 * TinySlider
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\TinySlider;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class TinySlider extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'far fa-images';

	/**
	 * @var array
	 */
	private $options = [
		'use_cdn'            => true,
		'axis'               => 'horizontal',
		'num_items'          => 1,
		'gutter'             => 0,
		'edge_padding'       => 0,
		'controls'           => true,
		'nav'                => true,
		'nav_as_thumbnails'  => false,
		'arrow_keys'         => false,
		'fixed_width'        => 0,
		'slide_by'           => 1,
		'speed'              => 300,
		'autoplay'           => true,
		'autoplay_timeout'   => 5000,
		'autoplay_direction' => 'forward',
		'loop'               => true,
		'rewind'             => false,
		'lazyload'           => false,
		'mouse_drag'         => false,
		'images'             => ''
	];

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['tiny_slider']['parameters'] = $this->options;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'tiny_slider')
			return;

		$data = Helpers::post()->only(['image_title', 'image_link']);

		$images = [];
		if (!empty($data) && !empty($data['image_title']) && !empty($data['image_link'])) {
			foreach ($data['image_title'] as $key => $item) {
				if (empty($link = $data['image_link'][$key]))
					continue;

				$images[] = [
					'title' => $item,
					'link'  => $link
				];
			}

			Helpers::post()->put('images', json_encode($images, JSON_UNESCAPED_UNICODE));
		}

		$parameters['use_cdn']            = FILTER_VALIDATE_BOOLEAN;
		$parameters['axis']               = FILTER_SANITIZE_STRING;
		$parameters['num_items']          = FILTER_VALIDATE_INT;
		$parameters['gutter']             = FILTER_VALIDATE_INT;
		$parameters['edge_padding']       = FILTER_VALIDATE_INT;
		$parameters['controls']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['nav']                = FILTER_VALIDATE_BOOLEAN;
		$parameters['nav_as_thumbnails']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['arrow_keys']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['fixed_width']        = FILTER_VALIDATE_INT;
		$parameters['slide_by']           = FILTER_VALIDATE_INT;
		$parameters['speed']              = FILTER_VALIDATE_INT;
		$parameters['autoplay']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['autoplay_timeout']   = FILTER_SANITIZE_STRING;
		$parameters['autoplay_direction'] = FILTER_SANITIZE_STRING;
		$parameters['loop']               = FILTER_VALIDATE_BOOLEAN;
		$parameters['rewind']             = FILTER_VALIDATE_BOOLEAN;
		$parameters['lazyload']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['mouse_drag']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['images']             = FILTER_DEFAULT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'tiny_slider')
			return;

		$context['posting_fields']['use_cdn']['label']['text'] = $txt['lp_tiny_slider']['use_cdn'];
		$context['posting_fields']['use_cdn']['label']['after'] = ' <img src="https://data.jsdelivr.com/v1/package/npm/tiny-slider/badge?style=rounded" alt="">';
		$context['posting_fields']['use_cdn']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'use_cdn',
				'checked' => !empty($context['lp_block']['options']['parameters']['use_cdn'])
			)
		);

		$context['posting_fields']['axis']['label']['text'] = $txt['lp_tiny_slider']['axis'];
		$context['posting_fields']['axis']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'axis'
			),
			'options' => array()
		);

		$axis_directions = array_combine(array('vertical', 'horizontal'), $txt['lp_panel_direction_set']);

		foreach ($axis_directions as $key => $value) {
			$context['posting_fields']['axis']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['axis']
			);
		}

		$context['posting_fields']['num_items']['label']['text'] = $txt['lp_tiny_slider']['num_items'];
		$context['posting_fields']['num_items']['input'] = array(
			'after' => $txt['lp_tiny_slider']['num_items_subtext'],
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_items',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_items']
			)
		);

		$context['posting_fields']['gutter']['label']['text'] = $txt['lp_tiny_slider']['gutter'];
		$context['posting_fields']['gutter']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'gutter',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['gutter']
			)
		);

		$context['posting_fields']['edge_padding']['label']['text'] = $txt['lp_tiny_slider']['edge_padding'];
		$context['posting_fields']['edge_padding']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'edge_padding',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['edge_padding']
			)
		);

		$context['posting_fields']['controls']['label']['text'] = $txt['lp_tiny_slider']['controls'];
		$context['posting_fields']['controls']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'controls',
				'checked' => !empty($context['lp_block']['options']['parameters']['controls'])
			)
		);

		$context['posting_fields']['nav']['label']['text'] = $txt['lp_tiny_slider']['nav'];
		$context['posting_fields']['nav']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'nav',
				'checked' => !empty($context['lp_block']['options']['parameters']['nav'])
			)
		);

		$context['posting_fields']['nav_as_thumbnails']['label']['text'] = $txt['lp_tiny_slider']['nav_as_thumbnails'];
		$context['posting_fields']['nav_as_thumbnails']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'nav_as_thumbnails',
				'checked' => !empty($context['lp_block']['options']['parameters']['nav_as_thumbnails'])
			)
		);

		$context['posting_fields']['arrow_keys']['label']['text'] = $txt['lp_tiny_slider']['arrow_keys'];
		$context['posting_fields']['arrow_keys']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'arrow_keys',
				'checked' => !empty($context['lp_block']['options']['parameters']['arrow_keys'])
			)
		);

		$context['posting_fields']['fixed_width']['label']['text'] = $txt['lp_tiny_slider']['fixed_width'];
		$context['posting_fields']['fixed_width']['input'] = array(
			'after' => $txt['zero_for_no_limit'],
			'type' => 'number',
			'attributes' => array(
				'id'    => 'fixed_width',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['fixed_width']
			)
		);

		$context['posting_fields']['slide_by']['label']['text'] = $txt['lp_tiny_slider']['slide_by'];
		$context['posting_fields']['slide_by']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'slide_by',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['slide_by']
			)
		);

		$context['posting_fields']['speed']['label']['text'] = $txt['lp_tiny_slider']['speed'];
		$context['posting_fields']['speed']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'speed',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['speed']
			)
		);

		$context['posting_fields']['autoplay']['label']['text'] = $txt['lp_tiny_slider']['autoplay'];
		$context['posting_fields']['autoplay']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'autoplay',
				'checked' => !empty($context['lp_block']['options']['parameters']['autoplay'])
			)
		);

		$context['posting_fields']['autoplay_timeout']['label']['text'] = $txt['lp_tiny_slider']['autoplay_timeout'];
		$context['posting_fields']['autoplay_timeout']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'autoplay_timeout',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['autoplay_timeout']
			)
		);

		$context['posting_fields']['autoplay_direction']['label']['text'] = $txt['lp_tiny_slider']['autoplay_direction'];
		$context['posting_fields']['autoplay_direction']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'autoplay_direction'
			),
			'options' => array()
		);

		$autoplay_directions = array_combine(array('forward', 'backward'), $txt['lp_tiny_slider']['autoplay_direction_set']);

		foreach ($autoplay_directions as $key => $value) {
			$context['posting_fields']['autoplay_direction']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['autoplay_direction']
			);
		}

		$context['posting_fields']['loop']['label']['text'] = $txt['lp_tiny_slider']['loop'];
		$context['posting_fields']['loop']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'loop',
				'checked' => !empty($context['lp_block']['options']['parameters']['loop'])
			)
		);

		$context['posting_fields']['rewind']['label']['text'] = $txt['lp_tiny_slider']['rewind'];
		$context['posting_fields']['rewind']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'rewind',
				'checked' => !empty($context['lp_block']['options']['parameters']['rewind'])
			)
		);

		$context['posting_fields']['lazyload']['label']['text'] = $txt['lp_tiny_slider']['lazyload'];
		$context['posting_fields']['lazyload']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'lazyload',
				'checked' => !empty($context['lp_block']['options']['parameters']['lazyload'])
			)
		);

		$context['posting_fields']['mouse_drag']['label']['text'] = $txt['lp_tiny_slider']['mouse_drag'];
		$context['posting_fields']['mouse_drag']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'mouse_drag',
				'checked' => !empty($context['lp_block']['options']['parameters']['mouse_drag'])
			)
		);

		$this->loadTemplate();

		addInlineJavaScript('
		function handleImages() {
			return {
				images: ' . ($context['lp_block']['options']['parameters']['images'] ?: '[]') . ',
				addNewImage() {
					this.images.push({
						link: "",
						title: ""
					})
				},
				removeImage(index) {
					this.images.splice(index, 1)
				}
			}
		}');

		$context['posting_fields']['images']['label']['html'] = $txt['lp_tiny_slider']['images'];
		$context['posting_fields']['images']['input']['html'] = tiny_slider_images();
		$context['posting_fields']['images']['input']['tab']  = 'content';
	}

	/**
	 * @param int $block_id
	 * @param array $parameters
	 * @return string
	 */
	public function getHtml(int $block_id, array $parameters): string
	{
		global $txt;

		if (empty($parameters['images']))
			return '';

		$html = '
		<div id="tiny_slider' . $block_id . '">';

		$images = json_decode($parameters['images'], true);

		foreach ($images as $image) {
			[$link, $title] = [$image['link'], $image['title']];

			$html .= '
			<div class="item">
				<img ' . (!empty($parameters['lazyload']) ? 'class="tns-lazy-img" data-' : '') . 'src="' . $link . '" alt="' . (!empty($title) ? $title : '') . '"' . (!empty($parameters['fixed_width']) ? ' width="' . $parameters['fixed_width'] . '"' : '') . '>';

			if (!empty($title)) {
				$html .= '
				<p>' . $title . '</p>';
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
				[$link, $title] = [$image['link'], $image['title']];

				$html .= '
				<li><img src="' . $link . '" alt="' . (!empty($title) ? $title : '') . '"></li>';
			}

			$html .= '
			</ul>';
		}

		if (!empty($parameters['controls'])) {
			$buttons = array_combine(array('prev', 'next'), $txt['lp_tiny_slider']['controls_buttons']);

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
	 * @param array $assets
	 * @return void
	 */
	public function prepareAssets(&$assets)
	{
		$assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
		$assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.min.js';
	}

	/**
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info;

		if ($type !== 'tiny_slider')
			return;

		$tiny_slider_html = Helpers::cache('tiny_slider_addon_b' . $block_id . '_' . $user_info['language'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getHtml', $block_id, $parameters);

		if (empty($tiny_slider_html))
			return;

		if (!empty($parameters['use_cdn'])) {
			loadCSSFile('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.min.js', array('external' => true));
		} else {
			loadCSSFile('light_portal/tiny_slider/tiny-slider.css');
			loadJavaScriptFile('light_portal/tiny_slider/tiny-slider.min.js', array('minimize' => true));
		}

		addInlineJavaScript('
			let slider' . $block_id . ' = tns({
				container: "#tiny_slider' . $block_id . '",
				axis: "' . (!empty($parameters['axis']) ? $parameters['axis'] : $this->options['axis']) . '",
				items: ' . (!empty($parameters['num_items']) ? $parameters['num_items'] : $this->options['num_items']) . ',
				gutter: ' . (!empty($parameters['gutter']) ? $parameters['gutter'] : $this->options['gutter']) . ',
				edgePadding: ' . (!empty($parameters['edge_padding']) ? $parameters['edge_padding'] : $this->options['edge_padding']) . ',
				fixedWidth: ' . (!empty($parameters['fixed_width']) ? $parameters['fixed_width'] : $this->options['fixed_width']) . ',
				slideBy: ' . (!empty($parameters['slide_by']) ? $parameters['slide_by'] : $this->options['slide_by']) . ',
				controls: ' . (!empty($parameters['controls']) ? 'true' : 'false') . ',
				controlsContainer: ".customize-controls",
				nav: ' . (!empty($parameters['nav']) ? 'true' : 'false') . ',
				navPosition: "bottom",' . (!empty($parameters['nav']) && !empty($parameters['nav_as_thumbnails']) ? '
				navContainer: ".customize-thumbnails",' : '') . '
				navAsThumbnails: ' . (!empty($parameters['nav_as_thumbnails']) ? 'true' : 'false') . ',
				arrowKeys: ' . (!empty($parameters['arrow_keys']) ? 'true' : 'false') . ',
				speed: ' . (!empty($parameters['speed']) ? $parameters['speed'] : $this->options['speed']) . ',
				autoplay: ' . (!empty($parameters['autoplay']) ? 'true' : 'false') . ',
				autoplayTimeout: ' . (!empty($parameters['autoplay_timeout']) ? $parameters['autoplay_timeout'] : $this->options['autoplay_timeout']) . ',
				autoplayDirection: "' . (!empty($parameters['autoplay_direction']) ? $parameters['autoplay_direction'] : $this->options['autoplay_direction']) . '",
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

		echo $tiny_slider_html;
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(array &$links)
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
