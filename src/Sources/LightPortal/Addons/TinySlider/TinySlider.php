<?php

/**
 * TinySlider.php
 *
 * @package TinySlider (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.12.23
 */

namespace Bugo\LightPortal\Addons\TinySlider;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, NumberField, RadioField, RangeField};

if (! defined('LP_NAME'))
	die('No direct access...');

class TinySlider extends Block
{
	public string $icon = 'far fa-images';

	private array $params = [
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

	public function blockOptions(array &$options): void
	{
		$options['tiny_slider']['parameters'] = $this->params;
	}

	public function validateBlockData(array &$parameters, string $type): void
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

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'tiny_slider')
			return;

		CustomField::make('images', $this->txt['lp_tiny_slider']['images'])
			->setTab('content')
			->setValue($this->getFromTemplate('tiny_slider_images'));

		RadioField::make('axis', $this->txt['lp_tiny_slider']['axis'])
			->setOptions(array_combine(['vertical', 'horizontal'], $this->txt['lp_panel_direction_set']))
			->setValue($this->context['lp_block']['options']['parameters']['axis']);

		RangeField::make('num_items', $this->txt['lp_tiny_slider']['num_items'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue($this->context['lp_block']['options']['parameters']['num_items']);

		NumberField::make('gutter', $this->txt['lp_tiny_slider']['gutter'])
			->setAttribute('min', 0)
			->setValue($this->context['lp_block']['options']['parameters']['gutter']);

		NumberField::make('edge_padding', $this->txt['lp_tiny_slider']['edge_padding'])
			->setAttribute('min', 0)
			->setValue($this->context['lp_block']['options']['parameters']['edge_padding']);

		CheckboxField::make('controls', $this->txt['lp_tiny_slider']['controls'])
			->setValue($this->context['lp_block']['options']['parameters']['controls']);

		CheckboxField::make('nav', $this->txt['lp_tiny_slider']['nav'])
			->setValue($this->context['lp_block']['options']['parameters']['nav']);

		CheckboxField::make('nav_as_thumbnails', $this->txt['lp_tiny_slider']['nav_as_thumbnails'])
			->setValue($this->context['lp_block']['options']['parameters']['nav_as_thumbnails']);

		CheckboxField::make('arrow_keys', $this->txt['lp_tiny_slider']['arrow_keys'])
			->setValue($this->context['lp_block']['options']['parameters']['arrow_keys']);

		NumberField::make('fixed_width', $this->txt['lp_tiny_slider']['fixed_width'])
			->setAfter($this->txt['zero_for_no_limit'])
			->setAttribute('min', 0)
			->setValue($this->context['lp_block']['options']['parameters']['fixed_width']);

		RangeField::make('slide_by', $this->txt['lp_tiny_slider']['slide_by'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue($this->context['lp_block']['options']['parameters']['slide_by']);

		NumberField::make('speed', $this->txt['lp_tiny_slider']['speed'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['speed']);

		CheckboxField::make('autoplay', $this->txt['lp_tiny_slider']['autoplay'])
			->setValue($this->context['lp_block']['options']['parameters']['autoplay']);

		NumberField::make('autoplay_timeout', $this->txt['lp_tiny_slider']['autoplay_timeout'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['autoplay_timeout']);

		RadioField::make('autoplay_direction', $this->txt['lp_tiny_slider']['autoplay_direction'])
			->setOptions(array_combine(['forward', 'backward'], $this->txt['lp_tiny_slider']['autoplay_direction_set']))
			->setValue($this->context['lp_block']['options']['parameters']['autoplay_direction']);

		CheckboxField::make('loop', $this->txt['lp_tiny_slider']['loop'])
			->setValue($this->context['lp_block']['options']['parameters']['loop']);

		CheckboxField::make('rewind', $this->txt['lp_tiny_slider']['rewind'])
			->setValue($this->context['lp_block']['options']['parameters']['rewind']);

		CheckboxField::make('lazyload', $this->txt['lp_tiny_slider']['lazyload'])
			->setValue($this->context['lp_block']['options']['parameters']['lazyload']);

		CheckboxField::make('mouse_drag', $this->txt['lp_tiny_slider']['mouse_drag'])
			->setValue($this->context['lp_block']['options']['parameters']['mouse_drag']);
	}

	public function getData(int|string $block_id, array $parameters): array
	{
		if (empty($parameters['images']))
			return [];

		$html = '
		<div id="tiny_slider' . $block_id . '">';

		$images = $this->jsonDecode($parameters['images']);

		foreach ($images as $image) {
			[$link, $title] = [$image['link'], $image['title']];

			$html .= /** @lang text */
				'
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
			<ul id="tiny_slider_thumbnails' . $block_id . '" class="thumbnails customize-thumbnails"' . (empty($parameters['controls']) ? '' : (' style="margin-bottom: -30px"')) . '>';

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

			$html .= /** @lang text */
				'
			<ul id="tiny_slider_controls' . $block_id . '" class="controls customize-controls">
				<li class="prev">
					<span class="button"><i class="fas fa-arrow-left"></i> ' . $buttons['prev'] . '</span>
				</li>
				<li class="next">
					<span class="button">' . $buttons['next'] . ' <i class="fas fa-arrow-right"></i></span>
				</li>
			</ul>';
		}

		$html .= '
		</div>';

		return ['content' => $html];
	}

	public function prepareAssets(array &$assets): void
	{
		$assets['css']['tiny_slider'][]     = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css';
		$assets['scripts']['tiny_slider'][] = 'https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js';
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'tiny_slider')
			return;

		$parameters['nav'] ??= false;
		$parameters['controls'] ??= false;
		$parameters['nav_as_thumbnails'] ??= false;

		$block_id = $data->block_id;

		$tiny_slider_html = $this->cache('tiny_slider_addon_b' . $block_id . '_' . $this->user_info['language'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $block_id, $parameters);

		if (empty($tiny_slider_html))
			return;

		$this->loadCSSFile('light_portal/tiny_slider/tiny-slider.css');
		$this->loadJavaScriptFile('light_portal/tiny_slider/tiny-slider.js', ['minimize' => true]);

		$this->addInlineJS('
			const slider' . $block_id . ' = tns({
				container: "#tiny_slider' . $block_id . '",
				axis: "' . (empty($parameters['axis']) ? $this->params['axis'] : $parameters['axis']) . '",
				items: ' . (empty($parameters['num_items']) ? $this->params['num_items'] : $parameters['num_items']) . ',
				gutter: ' . (empty($parameters['gutter']) ? $this->params['gutter'] : $parameters['gutter']) . ',
				edgePadding: ' . (empty($parameters['edge_padding']) ? $this->params['edge_padding'] : $parameters['edge_padding']) . ',
				fixedWidth: ' . (empty($parameters['fixed_width']) ? $this->params['fixed_width'] : $parameters['fixed_width']) . ',
				slideBy: ' . (empty($parameters['slide_by']) ? $this->params['slide_by'] : $parameters['slide_by']) . ',
				controls: ' . (empty($parameters['controls']) ? 'false' : 'true') . ',
				controlsContainer: "#tiny_slider_controls' . $block_id . '",
				nav: ' . (empty($parameters['nav']) ? 'false' : 'true') . ',
				navPosition: "bottom",' . ($parameters['nav'] && $parameters['nav_as_thumbnails'] ? '
				navContainer: "#tiny_slider_thumbnails' . $block_id . '",' : '') . '
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

	public function credits(array &$links): void
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
