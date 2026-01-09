<?php declare(strict_types=1);

/**
 * @package TinySlider (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.25
 */

namespace LightPortal\Plugins\TinySlider;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\AssetBuilder;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\UI\Fields\RadioField;
use LightPortal\UI\Fields\RangeField;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasView;
use Ramsey\Collection\Map\NamedParameterMap;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'far fa-images')]
class TinySlider extends Block
{
	use HasView;

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
		'images'             => '',
	];

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = $this->params;

		$data = $this->post()->only(['image_title', 'image_link']);

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

			$this->post()->put('images', json_encode($images, JSON_UNESCAPED_UNICODE));
		}
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'axis'               => FILTER_DEFAULT,
			'num_items'          => FILTER_VALIDATE_INT,
			'gutter'             => FILTER_VALIDATE_INT,
			'edge_padding'       => FILTER_VALIDATE_INT,
			'controls'           => FILTER_VALIDATE_BOOLEAN,
			'nav'                => FILTER_VALIDATE_BOOLEAN,
			'nav_as_thumbnails'  => FILTER_VALIDATE_BOOLEAN,
			'arrow_keys'         => FILTER_VALIDATE_BOOLEAN,
			'fixed_width'        => FILTER_VALIDATE_INT,
			'slide_by'           => FILTER_VALIDATE_INT,
			'speed'              => FILTER_VALIDATE_INT,
			'autoplay'           => FILTER_VALIDATE_BOOLEAN,
			'autoplay_timeout'   => FILTER_DEFAULT,
			'autoplay_direction' => FILTER_DEFAULT,
			'loop'               => FILTER_VALIDATE_BOOLEAN,
			'rewind'             => FILTER_VALIDATE_BOOLEAN,
			'lazyload'           => FILTER_VALIDATE_BOOLEAN,
			'mouse_drag'         => FILTER_VALIDATE_BOOLEAN,
			'images'             => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('images', $this->txt['images'])
			->setTab(Tab::CONTENT)
			->setValue($this->view(params: ['options' => $options]));

		RadioField::make('axis', $this->txt['axis'])
			->setOptions(array_combine(['vertical', 'horizontal'], Lang::$txt['lp_panel_direction_set']))
			->setValue($options['axis']);

		RangeField::make('num_items', $this->txt['num_items'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue($options['num_items']);

		NumberField::make('gutter', $this->txt['gutter'])
			->setAttribute('min', 0)
			->setValue($options['gutter']);

		NumberField::make('edge_padding', $this->txt['edge_padding'])
			->setAttribute('min', 0)
			->setValue($options['edge_padding']);

		CheckboxField::make('controls', $this->txt['controls'])
			->setValue($options['controls']);

		CheckboxField::make('nav', $this->txt['nav'])
			->setValue($options['nav']);

		CheckboxField::make('nav_as_thumbnails', $this->txt['nav_as_thumbnails'])
			->setValue($options['nav_as_thumbnails']);

		CheckboxField::make('arrow_keys', $this->txt['arrow_keys'])
			->setValue($options['arrow_keys']);

		NumberField::make('fixed_width', $this->txt['fixed_width'])
			->setDescription(Lang::$txt['zero_for_no_limit'])
			->setAttribute('min', 0)
			->setValue($options['fixed_width']);

		RangeField::make('slide_by', $this->txt['slide_by'])
			->setAttribute('min', 1)
			->setAttribute('max', 12)
			->setValue($options['slide_by']);

		NumberField::make('speed', $this->txt['speed'])
			->setAttribute('min', 1)
			->setValue($options['speed']);

		CheckboxField::make('autoplay', $this->txt['autoplay'])
			->setValue($options['autoplay']);

		NumberField::make('autoplay_timeout', $this->txt['autoplay_timeout'])
			->setAttribute('min', 1)
			->setValue($options['autoplay_timeout']);

		RadioField::make('autoplay_direction', $this->txt['autoplay_direction'])
			->setOptions(array_combine(['forward', 'backward'], $this->txt['autoplay_direction_set']))
			->setValue($options['autoplay_direction']);

		CheckboxField::make('loop', $this->txt['loop'])
			->setValue($options['loop']);

		CheckboxField::make('rewind', $this->txt['rewind'])
			->setValue($options['rewind']);

		CheckboxField::make('lazyload', $this->txt['lazyload'])
			->setValue($options['lazyload']);

		CheckboxField::make('mouse_drag', $this->txt['mouse_drag'])
			->setValue($options['mouse_drag']);
	}

	public function getData(int $id, NamedParameterMap $parameters): array
	{
		if (empty($parameters['images'])) {
			return [];
		}

		$tinySlider = Str::html('div', ['id' => $this->name . $id]);

		$images = Utils::jsonDecode($parameters['images'], true);

		foreach ($images as $image) {
			[$link, $title] = [$image['link'], $image['title']];

			$item = Str::html('div', ['class' => 'item']);
			$img = Str::html('img', [
				'src'   => $link,
				'alt'   => $title ?: '',
				'class' => empty($parameters['lazyload']) ? null : 'tns-lazy-img',
			]);

			if (! empty($parameters['fixed_width'])) {
				$img->setAttribute('width', $parameters['fixed_width']);
			}

			$item->addHtml($img);

			if ($title) {
				$item->addHtml(Str::html('p')->setText($title));
			}

			$tinySlider->addHtml($item);
		}

		$customizeTools = Str::html('div', ['class' => 'customize-tools']);

		if ($parameters['nav'] && $parameters['nav_as_thumbnails']) {
			$thumbnails = Str::html('ul', [
				'id'    => 'tiny_slider_thumbnails' . $id,
				'class' => 'thumbnails customize-thumbnails',
			]);

			if (! empty($parameters['controls'])) {
				$thumbnails->setAttribute('style', 'margin-bottom: -30px');
			}

			foreach ($images as $image) {
				[$link, $title] = [$image['link'], $image['title']];

				$thumbnails->addHtml(
					Str::html('li')->addHtml(Str::html('img', [
						'src' => $link,
						'alt' => $title ?: '',
					]))
				);
			}

			$customizeTools->addHtml($thumbnails);
		}

		if ($parameters['controls']) {
			$buttons = array_combine(['prev', 'next'], $this->txt['controls_buttons']);

			$controls = Str::html('ul', [
				'id'    => 'tiny_slider_controls' . $id,
				'class' => 'controls customize-controls',
			]);

			$controls->addHtml(Str::html('li', ['class' => 'prev'])
				->addHtml(Str::html('span', ['class' => 'button'])
					->addHtml(Str::html('i', ['class' => 'fas fa-arrow-left']))
					->addHtml(' ' . $buttons['prev'])));

			$controls->addHtml(Str::html('li', ['class' => 'next'])
				->addHtml(Str::html('span', ['class' => 'button'])
					->addHtml($buttons['next'] . ' ')
					->addHtml(Str::html('i', ['class' => 'fas fa-arrow-right']))));

			$customizeTools->addHtml($controls);
		}

		return ['content' => $tinySlider->toHtml() . $customizeTools->toHtml()];
	}

	public function prepareAssets(Event $e): void
	{
		$builder = new AssetBuilder($this);
		$builder->scripts()->add('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/min/tiny-slider.js');
		$builder->css()->add('https://cdn.jsdelivr.net/npm/tiny-slider@2/dist/tiny-slider.css');
		$builder->appendTo($e->args->assets);
	}

	public function prepareContent(Event $e): void
	{
		[$id, $parameters] = [$e->args->id, $e->args->parameters];

		$html = $this->langCache($this->name . '_addon_b' . $id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($id, $parameters));

		if (empty($html))
			return;

		Theme::loadCSSFile('light_portal/' . $this->name . '/tiny-slider.css');

		Theme::loadJavaScriptFile('light_portal/' . $this->name . '/tiny-slider.js', ['minimize' => true]);

		Theme::addInlineJavaScript('
			const slider' . $id . ' = tns({
				container: "#' . $this->name . $id . '",
				axis: "' . (empty($parameters['axis']) ? $this->params['axis'] : $parameters['axis']) . '",
				items: ' . (empty($parameters['num_items']) ? $this->params['num_items'] : $parameters['num_items']) . ',
				gutter: ' . (empty($parameters['gutter']) ? $this->params['gutter'] : $parameters['gutter']) . ',
				edgePadding: ' . (empty($parameters['edge_padding']) ? $this->params['edge_padding'] : $parameters['edge_padding']) . ',
				fixedWidth: ' . (empty($parameters['fixed_width']) ? $this->params['fixed_width'] : $parameters['fixed_width']) . ',
				slideBy: ' . (empty($parameters['slide_by']) ? $this->params['slide_by'] : $parameters['slide_by']) . ',
				controls: ' . (empty($parameters['controls']) ? 'false' : 'true') . ',
				controlsContainer: "#tiny_slider_controls' . $id . '",
				nav: ' . (empty($parameters['nav']) ? 'false' : 'true') . ',
				navPosition: "bottom",' . ($parameters['nav'] && $parameters['nav_as_thumbnails'] ? '
				navContainer: "#tiny_slider_thumbnails' . $id . '",' : '') . '
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

		echo $html['content'] ?? '';
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
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
