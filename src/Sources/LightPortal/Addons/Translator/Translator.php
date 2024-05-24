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
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\Translator;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, RadioField};

if (! defined('LP_NAME'))
	die('No direct access...');

class Translator extends Block
{
	public string $icon = 'fas fa-language';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'translator')
			return;

		$params = [
			'no_content_class' => true,
			'engine'           => 'google',
			'widget_theme'     => 'light',
			'auto_mode'        => false,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'translator')
			return;

		$params = [
			'engine'       => FILTER_DEFAULT,
			'widget_theme' => FILTER_DEFAULT,
			'auto_mode'    => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'translator')
			return;

		RadioField::make('engine', Lang::$txt['lp_translator']['engine'])
			->setOptions(array_combine(['google', 'yandex'], Lang::$txt['lp_translator']['engine_set']))
			->setValue(Utils::$context['lp_block']['options']['engine']);

		if (Utils::$context['lp_block']['options']['engine'] === 'google')
			return;

		RadioField::make('widget_theme', Lang::$txt['lp_translator']['widget_theme'])
			->setOptions(array_combine(['light', 'dark'], Lang::$txt['lp_translator']['widget_theme_set']))
			->setValue(Utils::$context['lp_block']['options']['widget_theme']);

		CheckboxField::make('auto_mode', Lang::$txt['lp_translator']['auto_mode'])
			->setValue(Utils::$context['lp_block']['options']['auto_mode']);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'translator')
			return;

		$parameters['auto_mode'] ??= false;

		if ($parameters['engine'] === 'yandex') {
			echo '
		<div id="ytWidget', $data->id, '" class="centertext noup"></div>
		<script src="https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget', $data->id, '&amp;pageLang=', substr(Config::$language ?? '', 0, 2), '&amp;widgetTheme=', $parameters['widget_theme'], '&amp;autoMode=', (bool) $parameters['auto_mode'], '"></script>';
		}

		if ($parameters['engine'] === 'google') {
			echo '
		<div id="google_translate_element', $data->id, /** @lang text */ '" class="centertext noup"></div>
		<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		<script>
			function googleTranslateElementInit() {
				new google.translate.TranslateElement({
					pageLanguage: "', substr(Config::$language ?? '', 0, 2), '"
				}, "google_translate_element', $data->id, '");
			}
		</script>';
		}
	}
}
