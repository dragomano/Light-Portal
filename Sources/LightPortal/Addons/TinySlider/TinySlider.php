<?php

/**
 * TinySlider.php
 *
 * @package TinySlider (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 09.05.23
 */

namespace Bugo\LightPortal\Addons\TinySlider;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class TinySlider extends Block
{
	public string $icon = 'far fa-images';

	private array $params = [
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

	public function blockOptions(array &$options)
	{
		$options['tiny_slider']['parameters'] = $this->params;
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'tiny_slider')
			return;

		$data = $this->request()->only(['image_title', 'image_link']);

		$images = [];
		if ($data && isset($data['image_title']) && isset($data['image_link'])) {
			foreach ($data['image_title'] as $key => $item) {
				if (empty($link = $data['image_link'][$key]))
					continue;

				$images[] = [
					'title' => $item,
					'link'  => $link
				];
			}

			$this->request()->put('images', json_encode($images, JSON_UNESCAPED_UNICODE));
		}

		$parameters['use_cdn']            = FILTER_VALIDATE_BOOLEAN;
		$parameters['axis']               = FILTER_DEFAULT;
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
		$parameters['autoplay_timeout']   = FILTER_DEFAULT;
		$parameters['autoplay_direction'] = FILTER_DEFAULT;
		$parameters['loop']               = FILTER_VALIDATE_BOOLEAN;
		$parameters['rewind']             = FILTER_VALIDATE_BOOLEAN;
		$parameters['lazyload']           = FILTER_VALIDATE_BOOLEAN;
		$parameters['mouse_drag']         = FILTER_VALIDATE_BOOLEAN;
		$parameters['images']             = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'tiny_slider')
			return;

		$this->context['posting_fields']['use_cdn']['label']['text'] = $this->txt['lp_tiny_slider']['use_cdn'];
		$this->context['posting_fields']['use_cdn']['label']['after'] = ' <img src="https://data.jsdelivr.com/v1/package/npm/tiny-slider/badge?style=rounded" alt="">';
		$this->context['posting_fields']['use_cdn']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'use_cdn',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['use_cdn']
			]
		];

		$this->context['posting_fields']['axis']['label']['text'] = $this->txt['lp_tiny_slider']['axis'];
		$this->context['posting_fields']['axis']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'axis'
			],
			'options' => []
		];

		$axis_directions = array_combine(['vertical', 'horizontal'], $this->txt['lp_panel_direction_set']);

		foreach ($axis_directions as $key => $value) {
			$this->context['posting_fields']['axis']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['axis']
			];
		}

		$this->context['posting_fields']['num_items']['label']['text'] = $this->txt['lp_tiny_slider']['num_items'];
		$this->context['posting_fields']['num_items']['input'] = [
			'after' => $this->txt['lp_tiny_slider']['num_items_subtext'],
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_items',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_items']
			]
		];

		$this->context['posting_fields']['gutter']['label']['text'] = $this->txt['lp_tiny_slider']['gutter'];
		$this->context['posting_fields']['gutter']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'gutter',
				'min'   => 0,
				'value' => $this->context['lp_block']['options']['parameters']['gutter']
			]
		];

		$this->context['posting_fields']['edge_padding']['label']['text'] = $this->txt['lp_tiny_slider']['edge_padding'];
		$this->context['posting_fields']['edge_padding']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'edge_padding',
				'min'   => 0,
				'value' => $this->context['lp_block']['options']['parameters']['edge_padding']
			]
		];

		$this->context['posting_fields']['controls']['label']['text'] = $this->txt['lp_tiny_slider']['controls'];
		$this->context['posting_fields']['controls']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'controls',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['controls']
			]
		];

		$this->context['posting_fields']['nav']['label']['text'] = $this->txt['lp_tiny_slider']['nav'];
		$this->context['posting_fields']['nav']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'nav',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['nav']
			]
		];

		$this->context['posting_fields']['nav_as_thumbnails']['label']['text'] = $this->txt['lp_tiny_slider']['nav_as_thumbnails'];
		$this->context['posting_fields']['nav_as_thumbnails']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'nav_as_thumbnails',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['nav_as_thumbnails']
			]
		];

		$this->context['posting_fields']['arrow_keys']['label']['text'] = $this->txt['lp_tiny_slider']['arrow_keys'];
		$this->context['posting_fields']['arrow_keys']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'arrow_keys',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['arrow_keys']
			]
		];

		$this->context['posting_fields']['fixed_width']['label']['text'] = $this->txt['lp_tiny_slider']['fixed_width'];
		$this->context['posting_fields']['fixed_width']['input'] = [
			'after' => $this->txt['zero_for_no_limit'],
			'type' => 'number',
			'attributes' => [
				'id'    => 'fixed_width',
				'min'   => 0,
				'value' => $this->context['lp_block']['options']['parameters']['fixed_width']
			]
		];

		$this->context['posting_fields']['slide_by']['label']['text'] = $this->txt['lp_tiny_slider']['slide_by'];
		$this->context['posting_fields']['slide_by']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'slide_by',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['slide_by']
			]
		];

		$this->context['posting_fields']['speed']['label']['text'] = $this->txt['lp_tiny_slider']['speed'];
		$this->context['posting_fields']['speed']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'speed',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['speed']
			]
		];

		$this->context['posting_fields']['autoplay']['label']['text'] = $this->txt['lp_tiny_slider']['autoplay'];
		$this->context['posting_fields']['autoplay']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'autoplay',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['autoplay']
			]
		];

		$this->context['posting_fields']['autoplay_timeout']['label']['text'] = $this->txt['lp_tiny_slider']['autoplay_timeout'];
		$this->context['posting_fields']['autoplay_timeout']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'autoplay_timeout',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['autoplay_timeout']
			]
		];

		$this->context['posting_fields']['autoplay_direction']['label']['text'] = $this->txt['lp_tiny_slider']['autoplay_direction'];
		$this->context['posting_fields']['autoplay_direction']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'autoplay_direction'
			],
			'options' => []
		];

		$autoplay_directions = array_combine(['forward', 'backward'], $this->txt['lp_tiny_slider']['autoplay_direction_set']);

		foreach ($autoplay_directions as $key => $value) {
			$this->context['posting_fields']['autoplay_direction']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['autoplay_direction']
			];
		}

		$this->context['posting_fields']['loop']['label']['text'] = $this->txt['lp_tiny_slider']['loop'];
		$this->context['posting_fields']['loop']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'loop',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['loop']
			]
		];

		$this->context['posting_fields']['rewind']['label']['text'] = $this->txt['lp_tiny_slider']['rewind'];
		$this->context['posting_fields']['rewind']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'rewind',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['rewind']
			]
		];

		$this->context['posting_fields']['lazyload']['label']['text'] = $this->txt['lp_tiny_slider']['lazyload'];
		$this->context['posting_fields']['lazyload']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'lazyload',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['lazyload']
			]
		];

		$this->context['posting_fields']['mouse_drag']['label']['text'] = $this->txt['lp_tiny_slider']['mouse_drag'];
		$this->context['posting_fields']['mouse_drag']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'mouse_drag',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['mouse_drag']
			]
		];

		$this->setTemplate();

		$this->addInlineJavaScript('
		function handleImages() {
			return {
				images: ' . ($this->context['lp_block']['options']['parameters']['images'] ?: '[]') . ',
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

		$this->context['posting_fields']['images']['label']['html'] = $this->txt['lp_tiny_slider']['images'];
		$this->context['posting_fields']['images']['input']['html'] = tiny_slider_images();
		$this->context['posting_fields']['images']['input']['tab']  = 'content';
	}

	public function getData(int $block_id, array $parameters): array
	{
		if (empty($parameters['images']))
			return [];

		$html = '
		<div id="tiny_slider' . $block_id . '">';

		$images = $this->jsonDecode($parameters['images'], true);

		foreach ($images as $image) {
			[$link, $title] = [$image['link'], $image['title']];

			$html .= '
			<div class="item">
				<img ' . (empty($parameters['lazyload']) ? '' : 'class="tns-lazy-img" data-') . 'src="' . $link . '" alt="' . ($title ?: '') . '"' . (empty($parameters['fixed_width']) ? '' : (' width="' . $parameters['fixed_width'] . '"')) . '>';

			if ($title) {
				$html .= '
				<p>' . $title . '</p>';
			}

			$html .= '
			</div>';
		}

		$html .= '
		</div>
		<div class="customize-tools">';

		if ($parameters['nav'] && $parameters['nav_as_thumbnails']) {
			$html .= '
			<ul class="thumbnails customize-thumbnails"' . (empty($parameters['controls']) ? '' : (' style="margin-bottom: -30px"')) . '>';

			foreach ($images as $image) {
				[$link, $title] = [$image['link'], $image['title']];

				$html .= '
				<li><img src="' . $link . '" alt="' . ($title ?: '') . '"></li>';
			}

			$html .= '
			</ul>';
		}

		if ($parameters['controls']) {
			$buttons = array_combine(['prev', 'next'], $this->txt['lp_tiny_slider']['controls_buttons']);

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

		return ['content' => $html];
	}

	public function prepareAssets(array &$assets)
	{
		$assets['css']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
		$assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.min.js';
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'tiny_slider')
			return;

		$tiny_slider_html = $this->cache('tiny_slider_addon_b' . $block_id . '_' . $this->user_info['language'])
			->setLifeTime($cache_time)
			->setFallback(self::class, 'getData', $block_id, $parameters);

		if (empty($tiny_slider_html))
			return;

		if ($parameters['use_cdn']) {
			$this->loadExtCSS('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css');
			$this->loadExtJS('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js');
		} else {
			$this->loadCSSFile('light_portal/tiny_slider/tiny-slider.css');
			$this->loadJavaScriptFile('light_portal/tiny_slider/tiny-slider.min.js', ['minimize' => true]);
		}

		$this->addInlineJavaScript('
			let slider' . ($this->request()->has('preview') ? uniqid() : $block_id) . ' = tns({
				container: "#tiny_slider' . $block_id . '",
				axis: "' . (empty($parameters['axis']) ? $this->params['axis'] : $parameters['axis']) . '",
				items: ' . (empty($parameters['num_items']) ? $this->params['num_items'] : $parameters['num_items']) . ',
				gutter: ' . (empty($parameters['gutter']) ? $this->params['gutter'] : $parameters['gutter']) . ',
				edgePadding: ' . (empty($parameters['edge_padding']) ? $this->params['edge_padding'] : $parameters['edge_padding']) . ',
				fixedWidth: ' . (empty($parameters['fixed_width']) ? $this->params['fixed_width'] : $parameters['fixed_width']) . ',
				slideBy: ' . (empty($parameters['slide_by']) ? $this->params['slide_by'] : $parameters['slide_by']) . ',
				controls: ' . (empty($parameters['controls']) ? 'false' : 'true') . ',
				controlsContainer: ".customize-controls",
				nav: ' . (empty($parameters['nav']) ? 'false' : 'true') . ',
				navPosition: "bottom",' . ($parameters['nav'] && $parameters['nav_as_thumbnails'] ? '
				navContainer: ".customize-thumbnails",' : '') . '
				navAsThumbnails: ' . (empty($parameters['nav_as_thumbnails']) ? 'false' : 'true') . ',
				arrowKeys: ' . (empty($parameters['arrow_keys']) ? 'false' : 'true') . ',
				speed: ' . (empty($parameters['speed']) ? $this->params['speed'] : $parameters['speed']) . ',
				autoplay: ' . (empty($parameters['autoplay']) ? 'false' : 'true') . ',
				autoplayTimeout: ' . (empty($parameters['autoplay_timeout']) ? $this->params['autoplay_timeout'] : $parameters['autoplay_timeout']) . ',
				autoplayDirection: "' . (empty($parameters['autoplay_direction']) ? $this->params['autoplay_direction'] : $parameters['autoplay_direction']) . '",
				autoplayHoverPause: true,
				autoplayButtonOutput: false,
				loop: ' . (empty($parameters['loop']) ? 'false' : 'true') . ',
				rewind: ' . (empty($parameters['rewind']) ? 'false' : 'true') . ',
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
				lazyload: ' . (empty($parameters['lazyload']) ? 'false' : 'true') . ',
				mouseDrag: ' . (empty($parameters['mouse_drag']) ? 'false' : 'true') . ',
				freezable: false
			});', true);

		echo $tiny_slider_html['content'] ?? '';
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Tiny Slider 2',
			'link' => 'https://github.com/ganlanyuan/tiny-slider',
			'author' => 'William Lin',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/ganlanyuan/tiny-slider/blob/master/LICENSE'
			]
		];
	}
}
