<?php

/**
 * @package Swiper (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\Swiper;

use Bugo\Compat\{Lang, Theme, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField};
use Bugo\LightPortal\Areas\Fields\{RadioField, RangeField, SelectField};
use Bugo\LightPortal\Enums\Tab;

if (! defined('LP_NAME'))
	die('No direct access...');

class Swiper extends Block
{
	public string $icon = 'far fa-images';

	private array $effects = ['slide', 'fade', 'cube', 'coverflow', 'flip', 'cards', 'creative'];

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'swiper')
			return;

		$params = [
			'direction'       => 'horizontal',
			'effect'          => 'coverflow',
			'slides_per_view' => 3,
			'loop'            => true,
			'show_pagination' => true,
			'show_navigation' => true,
			'show_scrollbar'  => true,
			'images'          => '',
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'swiper')
			return;

		$data = $this->request()->only(['image_title', 'image_link']);

		$images = [];
		if ($data && isset($data['image_title']) && isset($data['image_link'])) {
			foreach ($data['image_title'] as $key => $item) {
				if (empty($link = $data['image_link'][$key]))
					continue;

				$images[] = [
					'title' => $item,
					'link'  => $link,
				];
			}

			$this->request()->put('images', json_encode($images, JSON_UNESCAPED_UNICODE));
		}

		$params = [
			'direction'       => FILTER_DEFAULT,
			'effect'          => FILTER_DEFAULT,
			'slides_per_view' => FILTER_VALIDATE_INT,
			'loop'            => FILTER_VALIDATE_BOOLEAN,
			'show_pagination' => FILTER_VALIDATE_BOOLEAN,
			'show_navigation' => FILTER_VALIDATE_BOOLEAN,
			'show_scrollbar'  => FILTER_VALIDATE_BOOLEAN,
			'images'          => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'swiper')
			return;

		CustomField::make('images', Lang::$txt['lp_swiper']['images'])
			->setTab(Tab::CONTENT)
			->setValue($this->getFromTemplate('swiper_images'));

		RadioField::make('direction', Lang::$txt['lp_swiper']['direction'])
			->setOptions(array_combine(['vertical', 'horizontal'], Lang::$txt['lp_panel_direction_set']))
			->setValue(Utils::$context['lp_block']['options']['direction']);

		SelectField::make('effect', Lang::$txt['lp_swiper']['effect'])
			->setOptions(array_combine($this->effects, $this->effects))
			->setValue(Utils::$context['lp_block']['options']['effect']);

		RangeField::make('slides_per_view', Lang::$txt['lp_swiper']['slides_per_view'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue(Utils::$context['lp_block']['options']['slides_per_view']);

		CheckboxField::make('loop', Lang::$txt['lp_swiper']['loop'])
			->setValue(Utils::$context['lp_block']['options']['loop']);

		CheckboxField::make('show_pagination', Lang::$txt['lp_swiper']['show_pagination'])
			->setValue(Utils::$context['lp_block']['options']['show_pagination']);

		CheckboxField::make('show_navigation', Lang::$txt['lp_swiper']['show_navigation'])
			->setValue(Utils::$context['lp_block']['options']['show_navigation']);

		CheckboxField::make('show_scrollbar', Lang::$txt['lp_swiper']['show_scrollbar'])
			->setValue(Utils::$context['lp_block']['options']['show_scrollbar']);
	}

	public function getData(int|string $id, array $parameters): array
	{
		if (empty($parameters['images']))
			return [];

		$html = '
		<div id="swiper' . $id . '" class="swiper"' . (Utils::$context['right_to_left'] ? ' dir="rtl"' : '') . '>
			<div class="swiper-wrapper">';

		$images = Utils::jsonDecode($parameters['images'], true);

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
			<div id="swiper-pagination' . $id . '" class="swiper-pagination"></div>';

		if (! empty($parameters['show_navigation']))
			$html .= '
			<div id="swiper-button-prev' . $id . '" class="swiper-button-prev"></div>
			<div id="swiper-button-next' . $id . '" class="swiper-button-next"></div>';

		if (! empty($parameters['show_scrollbar']))
			$html .= '
			<div id="swiper-scrollbar' . $id . '" class="swiper-scrollbar"></div>';

		$html .= '
		</div>';

		return ['content' => $html];
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'swiper')
			return;

		$id = $data->id;

		$swiperHtml = $this->cache('swiper_addon_b' . $id . '_' . User::$info['language'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $id, $parameters);

		if (empty($swiperHtml))
			return;

		Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css', ['external' => true]);
		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', ['external' => true]);

		Theme::addInlineJavaScript('
			const swiper' . $id . ' = new Swiper("#swiper' . $id . '", {
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
					el: "#swiper-pagination' . $id . '",
					dynamicBullets: true,
					clickable: true,
				},') . (empty($parameters['show_navigation']) ? '' : '
				navigation: {
					nextEl: "#swiper-button-next' . $id . '",
					prevEl: "#swiper-button-prev' . $id . '",
				},') . (empty($parameters['show_scrollbar']) ? '' : '
				scrollbar: {
					el: "#swiper-scrollbar' . $id . '",
				},') . '
			});', true);

		echo $swiperHtml['content'] ?? '';
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
