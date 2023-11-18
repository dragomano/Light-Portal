<?php

/**
 * Swiper.php
 *
 * @package Swiper (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 12.11.23
 */

namespace Bugo\LightPortal\Addons\Swiper;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class Swiper extends Block
{
	public string $icon = 'far fa-images';

	private array $params = [
		'direction'       => 'horizontal',
		'effect'          => 'coverflow',
		'slides_per_view' => 3,
		'loop'            => true,
		'show_pagination' => true,
		'show_navigation' => true,
		'show_scrollbar'  => true,
		'images'          => '',
	];

	private array $effects = ['slide', 'fade', 'cube', 'coverflow', 'flip', 'cards', 'creative'];

	public function blockOptions(array &$options): void
	{
		$options['swiper']['parameters'] = $this->params;
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'swiper')
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

		$parameters['direction']       = FILTER_DEFAULT;
		$parameters['effect']          = FILTER_DEFAULT;
		$parameters['slides_per_view'] = FILTER_VALIDATE_INT;
		$parameters['loop']            = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_pagination'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_navigation'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_scrollbar']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['images']          = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'swiper')
			return;

		$this->context['posting_fields']['direction']['label']['text'] = $this->txt['lp_swiper']['direction'];
		$this->context['posting_fields']['direction']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'direction'
			],
			'options' => []
		];

		$directions = array_combine(['vertical', 'horizontal'], $this->txt['lp_panel_direction_set']);

		foreach ($directions as $key => $value) {
			$this->context['posting_fields']['direction']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['direction']
			];
		}

		$this->context['posting_fields']['effect']['label']['text'] = $this->txt['lp_swiper']['effect'];
		$this->context['posting_fields']['effect']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id'      => 'effect',
			],
			'options' => [],
		];

		$effects = array_combine($this->effects, $this->effects);

		foreach ($effects as $value => $title) {
			$this->context['posting_fields']['effect']['input']['options'][$title] = [
				'value'    => $value,
				'selected' => $value == $this->context['lp_block']['options']['parameters']['effect']
			];
		}

		$this->context['posting_fields']['slides_per_view']['label']['text'] = $this->txt['lp_swiper']['slides_per_view'];
		$this->context['posting_fields']['slides_per_view']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'slides_per_view',
				'value' => $this->context['lp_block']['options']['parameters']['slides_per_view']
			]
		];

		$this->context['posting_fields']['loop']['label']['text'] = $this->txt['lp_swiper']['loop'];
		$this->context['posting_fields']['loop']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'loop',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['loop']
			]
		];

		$this->context['posting_fields']['show_pagination']['label']['text'] = $this->txt['lp_swiper']['show_pagination'];
		$this->context['posting_fields']['show_pagination']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_pagination',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_pagination']
			]
		];

		$this->context['posting_fields']['show_navigation']['label']['text'] = $this->txt['lp_swiper']['show_navigation'];
		$this->context['posting_fields']['show_navigation']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_navigation',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_navigation']
			]
		];

		$this->context['posting_fields']['show_scrollbar']['label']['text'] = $this->txt['lp_swiper']['show_scrollbar'];
		$this->context['posting_fields']['show_scrollbar']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_scrollbar',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_scrollbar']
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

		$this->context['posting_fields']['images']['label']['html'] = $this->txt['lp_swiper']['images'];
		$this->context['posting_fields']['images']['input']['html'] = swiper_images();
		$this->context['posting_fields']['images']['input']['tab']  = 'content';
	}

	public function getData(int|string $block_id, array $parameters): array
	{
		if (empty($parameters['images']))
			return [];

		$html = '
		<div id="swiper' . $block_id . '" class="swiper"' . ($this->context['right_to_left'] ? ' dir="rtl"' : '') . '>
			<div class="swiper-wrapper">';

		$images = $this->jsonDecode($parameters['images']);

		foreach ($images as $image) {
			[$link, $title] = [$image['link'], $image['title']];

			$html .= '
				<div class="swiper-slide">
					<img src="' . $link . '" alt="' . ($title ?: '') . '" loading="lazy">';

			if ($title) {
				$html .= '
					<p>' . $title . '</p>';
			}

			$html .= '
				</div>';
		}

		$html .= '
			</div>';

		if (! empty($parameters['show_pagination']))
			$html .= '
			<div id="swiper-pagination' . $block_id . '" class="swiper-pagination"></div>';

		if (! empty($parameters['show_navigation']))
			$html .= '
			<div id="swiper-button-prev' . $block_id . '" class="swiper-button-prev"></div>
			<div id="swiper-button-next' . $block_id . '" class="swiper-button-next"></div>';

		if (! empty($parameters['show_scrollbar']))
			$html .= '
			<div id="swiper-scrollbar' . $block_id . '" class="swiper-scrollbar"></div>';

		$html .= '
		</div>';

		return ['content' => $html];
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'swiper')
			return;

		$block_id = $this->request()->has('preview') ? uniqid() : $data->block_id;

		$swiper_html = $this->cache('swiper_addon_b' . $block_id . '_' . $this->user_info['language'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $block_id, $parameters);

		if (empty($swiper_html))
			return;

		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js');

		$this->addInlineJavaScript('
			const swiper' . $block_id . ' = new Swiper("#swiper' . $block_id . '", {
				direction: "' . ($parameters['direction'] ?? 'horizontal') . '",
				loop: ' . (empty($parameters['loop']) ? 'false' : 'true') . ',
				effect: "' . ($parameters['effect'] ?? 'coverflow') . '",
				lazy: true,
				grabCursor: true,' . ($parameters['effect'] === 'cards' ? '
				centeredSlides: true,' : '') . '
				slidesPerView: ' . ($parameters['slides_per_view'] ?? '"auto"') . ',
				coverflowEffect: {
					rotate: 50,
					stretch: 0,
					depth: 100,
					modifier: 1,
					slideShadows: true,
				},
				creativeEffect: {
					prev: {
						translate: [0, 0, -400],
					},
					next: {
						translate: ["100%", 0, 0],
					},
				},
				keyboard: {
					enabled: true,
				},
				mousewheel: true,
				autoplay: {
					delay: 2500,
					disableOnInteraction: false,
				},
				spaceBetween: 10,' . (empty($parameters['show_pagination']) ? '' : '
				pagination: {
					el: "#swiper-pagination' . $block_id . '",
					dynamicBullets: true,
					clickable: true,
				},') . (empty($parameters['show_navigation']) ? '' : '
				navigation: {
					nextEl: "#swiper-button-next' . $block_id . '",
					prevEl: "#swiper-button-prev' . $block_id . '",
				},') . (empty($parameters['show_scrollbar']) ? '' : '
				scrollbar: {
					el: "#swiper-scrollbar' . $block_id . '",
				},') . '
			});', true);

		echo $swiper_html['content'] ?? '';
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'Swiper',
			'link' => 'https://github.com/nolimits4web/swiper',
			'author' => 'Vladimir Kharlampidi',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/nolimits4web/swiper/blob/master/LICENSE'
			]
		];
	}
}
