<?php

/**
 * @package Swiper (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\Swiper;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\UI\Fields\RangeField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class Swiper extends Block
{
	public string $icon = 'far fa-images';

	private array $effects = ['slide', 'fade', 'cube', 'coverflow', 'flip', 'cards', 'creative'];

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
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

	public function validateBlockParams(Event $e): void
	{
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

		$e->args->params = [
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

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('images', $this->txt['images'])
			->setTab(Tab::CONTENT)
			->setValue($this->getFromTemplate('swiper_images', $options));

		RadioField::make('direction', $this->txt['direction'])
			->setOptions(array_combine(['vertical', 'horizontal'], Lang::$txt['lp_panel_direction_set']))
			->setValue($options['direction']);

		SelectField::make('effect', $this->txt['effect'])
			->setOptions(array_combine($this->effects, $this->effects))
			->setValue($options['effect']);

		RangeField::make('slides_per_view', $this->txt['slides_per_view'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue($options['slides_per_view']);

		CheckboxField::make('loop', $this->txt['loop'])
			->setValue($options['loop']);

		CheckboxField::make('show_pagination', $this->txt['show_pagination'])
			->setValue($options['show_pagination']);

		CheckboxField::make('show_navigation', $this->txt['show_navigation'])
			->setValue($options['show_navigation']);

		CheckboxField::make('show_scrollbar', $this->txt['show_scrollbar'])
			->setValue($options['show_scrollbar']);
	}

	public function getData(int|string $id, array $parameters): array
	{
		if (empty($parameters['images']))
			return [];

		$swiper = Str::html('div', [
			'id' => 'swiper' . $id,
			'class' => 'swiper',
			'dir' => Utils::$context['right_to_left'] ? 'rtl' : null,
		]);

		$wrapper = Str::html('div', ['class' => 'swiper-wrapper']);

		$images = Utils::jsonDecode($parameters['images'], true);

		foreach ($images as $image) {
			[$link, $title] = [$image['link'], $image['title']];

			$slide = Str::html('div', ['class' => 'swiper-slide']);
			$img = Str::html('img', [
				'src' => $link,
				'alt' => $title ?: '',
				'loading' => 'lazy',
			]);

			$slide->addHtml($img);

			if ($title) {
				$slide->addHtml(Str::html('p')->setText($title));
			}

			$wrapper->addHtml($slide);
		}

		$swiper->addHtml($wrapper);

		if (! empty($parameters['show_pagination'])) {
			$swiper->addHtml(Str::html('div', [
				'id' => 'swiper-pagination' . $id,
				'class' => 'swiper-pagination',
			]));
		}

		if (! empty($parameters['show_navigation'])) {
			$swiper->addHtml(Str::html('div', [
				'id' => 'swiper-button-prev' . $id,
				'class' => 'swiper-button-prev',
			]));
			$swiper->addHtml(Str::html('div', [
				'id' => 'swiper-button-next' . $id,
				'class' => 'swiper-button-next',
			]));
		}

		if (! empty($parameters['show_scrollbar'])) {
			$swiper->addHtml(Str::html('div', [
				'id' => 'swiper-scrollbar' . $id,
				'class' => 'swiper-scrollbar',
			]));
		}

		return ['content' => $swiper->toHtml()];
	}

	public function prepareContent(Event $e): void
	{
		[$id, $parameters] = [$e->args->id, $e->args->parameters];

		$swiperHtml = $this->cache($this->name . '_addon_b' . $id . '_' . User::$info['language'])
			->setLifeTime($e->args->cacheTime)
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

	public function credits(Event $e): void
	{
		$e->args->links[] = [
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
