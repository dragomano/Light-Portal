<?php

/**
 * Translator.php
 *
 * @package Translator (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 27.02.22
 */

namespace Bugo\LightPortal\Addons\Translator;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Translator extends Plugin
{
	public string $icon = 'fas fa-language';

	public function blockOptions(array &$options)
	{
		$options['translator']['no_content_class'] = true;

		$options['translator']['parameters'] = [
			'engine'       => 'google',
			'widget_theme' => 'light',
			'auto_mode'    => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'translator')
			return;

		$parameters['engine']       = FILTER_DEFAULT;
		$parameters['widget_theme'] = FILTER_DEFAULT;
		$parameters['auto_mode']    = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'translator')
			return;

		$this->context['posting_fields']['engine']['label']['text'] = $this->txt['lp_translator']['engine'];
		$this->context['posting_fields']['engine']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'engine'
			],
			'options' => []
		];

		$engines = array_combine(['google', 'yandex'], $this->txt['lp_translator']['engine_set']);

		foreach ($engines as $key => $value) {
			$this->context['posting_fields']['engine']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['engine']
			];
		}

		if ($this->context['lp_block']['options']['parameters']['engine'] == 'google')
			return;

		$this->context['posting_fields']['widget_theme']['label']['text'] = $this->txt['lp_translator']['widget_theme'];
		$this->context['posting_fields']['widget_theme']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'widget_theme'
			]
		];

		$this->context['posting_fields']['widget_theme']['input']['options'] = [
			'light' => [
				'value'    => 'light',
				'selected' => 'light' == $this->context['lp_block']['options']['parameters']['widget_theme']
			],
			'dark' => [
				'value'    => 'dark',
				'selected' => 'dark' == $this->context['lp_block']['options']['parameters']['widget_theme']
			]
		];

		$this->context['posting_fields']['auto_mode']['label']['text'] = $this->txt['lp_translator']['auto_mode'];
		$this->context['posting_fields']['auto_mode']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'auto_mode',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['auto_mode']
			]
		];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'translator')
			return;

		if ($parameters['engine'] === 'yandex') {
			echo '
		<div id="ytWidget', $block_id, '" class="centertext noup"></div>
		<script src="https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget', $block_id, '&amp;pageLang=', substr($this->language, 0, 2), '&amp;widgetTheme=', $parameters['widget_theme'], '&amp;autoMode=', (bool) $parameters['auto_mode'], '"></script>';
		} else {
			echo '
		<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		<div class="centertext noup">
			<div id="google_translate_element', $block_id, '"></div>
			<script>
				function googleTranslateElementInit() {
					new google.translate.TranslateElement({
						pageLanguage: "', substr($this->language, 0, 2), '"
					}, "google_translate_element', $block_id, '");
				}
			</script>
		</div>';
		}
	}
}
