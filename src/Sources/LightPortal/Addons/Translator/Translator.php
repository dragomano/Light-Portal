<?php

/**
 * Translator.php
 *
 * @package Translator (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.12.23
 */

namespace Bugo\LightPortal\Addons\Translator;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\RadioField;

if (! defined('LP_NAME'))
	die('No direct access...');

class Translator extends Block
{
	public string $icon = 'fas fa-language';

	public function blockOptions(array &$options): void
	{
		$options['translator']['no_content_class'] = true;

		$options['translator']['parameters'] = [
			'engine'       => 'google',
			'widget_theme' => 'light',
			'auto_mode'    => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'translator')
			return;

		$parameters['engine']       = FILTER_DEFAULT;
		$parameters['widget_theme'] = FILTER_DEFAULT;
		$parameters['auto_mode']    = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'translator')
			return;

		RadioField::make('engine', $this->txt['lp_translator']['engine'])
			->setOptions(array_combine(['google', 'yandex'], $this->txt['lp_translator']['engine_set']))
			->setValue($this->context['lp_block']['options']['parameters']['engine']);

		if ($this->context['lp_block']['options']['parameters']['engine'] === 'google')
			return;

		RadioField::make('widget_theme', $this->txt['lp_translator']['widget_theme'])
			->setOptions(array_combine(['light', 'dark'], $this->txt['lp_translator']['widget_theme_set']))
			->setValue($this->context['lp_block']['options']['parameters']['widget_theme']);

		CheckboxField::make('auto_mode', $this->txt['lp_translator']['auto_mode'])
			->setValue($this->context['lp_block']['options']['parameters']['auto_mode']);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'translator')
			return;

		$parameters['auto_mode'] ??= false;

		if ($parameters['engine'] === 'yandex') {
			echo '
		<div id="ytWidget', $data->block_id, /** @lang text */ '" class="centertext noup"></div>
		<script src="https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget', $data->block_id, '&amp;pageLang=', substr($this->language, 0, 2), '&amp;widgetTheme=', $parameters['widget_theme'], '&amp;autoMode=', (bool) $parameters['auto_mode'], '"></script>';
		}

		if ($parameters['engine'] === 'google') {
			echo /** @lang text */ '
		<div id="google_translate_element', $data->block_id, /** @lang text */ '" class="centertext noup"></div>
		<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		<script>
			function googleTranslateElementInit() {
				new google.translate.TranslateElement({
					pageLanguage: "', substr($this->language, 0, 2), '"
				}, "google_translate_element', $data->block_id, /** @lang text */ '");
			}
		</script>';
		}
	}
}
