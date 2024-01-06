<?php

/**
 * Swiper.php
 *
 * @package Swiper (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.12.23
 */

namespace Bugo\LightPortal\Addons\Swiper;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, RadioField, RangeField, SelectField};

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

		CustomField::make('images', $this->txt['lp_swiper']['images'])
			->setTab('content')
			->setValue($this->getFromTemplate('swiper_images'));

		RadioField::make('direction', $this->txt['lp_swiper']['direction'])
			->setOptions(array_combine(['vertical', 'horizontal'], $this->txt['lp_panel_direction_set']))
			->setValue($this->context['lp_block']['options']['parameters']['direction']);

		SelectField::make('effect', $this->txt['lp_swiper']['effect'])
			->setOptions(array_combine($this->effects, $this->effects))
			->setValue($this->context['lp_block']['options']['parameters']['effect']);

		RangeField::make('slides_per_view', $this->txt['lp_swiper']['slides_per_view'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue($this->context['lp_block']['options']['parameters']['slides_per_view']);

		CheckboxField::make('loop', $this->txt['lp_swiper']['loop'])
			->setValue($this->context['lp_block']['options']['parameters']['loop']);

		CheckboxField::make('show_pagination', $this->txt['lp_swiper']['show_pagination'])
			->setValue($this->context['lp_block']['options']['parameters']['show_pagination']);

		CheckboxField::make('show_navigation', $this->txt['lp_swiper']['show_navigation'])
			->setValue($this->context['lp_block']['options']['parameters']['show_navigation']);

		CheckboxField::make('show_scrollbar', $this->txt['lp_swiper']['show_scrollbar'])
			->setValue($this->context['lp_block']['options']['parameters']['show_scrollbar']);
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

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'swiper')
			return;

		$block_id = $data->block_id;

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
