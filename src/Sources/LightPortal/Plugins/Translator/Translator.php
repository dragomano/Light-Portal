<?php

/**
 * @package Translator (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\Translator;

use Bugo\Compat\Config;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class Translator extends Block
{
	public string $icon = 'fas fa-language';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'no_content_class' => true,
			'engine'           => 'google',
			'widget_theme'     => 'light',
			'auto_mode'        => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'engine'       => FILTER_DEFAULT,
			'widget_theme' => FILTER_DEFAULT,
			'auto_mode'    => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		RadioField::make('engine', $this->txt['engine'])
			->setOptions(array_combine(['google', 'yandex'], $this->txt['engine_set']))
			->setValue($options['engine']);

		if ($options['engine'] === 'google')
			return;

		RadioField::make('widget_theme', $this->txt['widget_theme'])
			->setOptions(array_combine(['light', 'dark'], $this->txt['widget_theme_set']))
			->setValue($options['widget_theme']);

		CheckboxField::make('auto_mode', $this->txt['auto_mode'])
			->setValue($options['auto_mode']);
	}

	public function prepareContent(Event $e): void
	{
		$id = $e->args->id;

		$parameters = $e->args->parameters;
		$parameters['auto_mode'] ??= false;

		if ($parameters['engine'] === 'yandex') {
			echo Str::html('div')
				->id('ytWidget' . $id)
				->class('centertext noup');

			echo Str::html('script')->src(
				implode('', [
					'https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget',
					$id . '&amp;pageLang=',
					substr(Config::$language ?? '', 0, 2),
					'&amp;widgetTheme=' . $parameters['widget_theme'],
					'&amp;autoMode=' . (bool) $parameters['auto_mode'],
				])
			);
		}

		if ($parameters['engine'] === 'google') {
			echo Str::html('div')
				->id('google_translate_element' . $id)
				->class('centertext noup');

			echo Str::html('script')
				->src('https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit');

			echo Str::html('script')->setText('
			    function googleTranslateElementInit() {
			        new google.translate.TranslateElement({
			            pageLanguage: "' . substr(Config::$language ?? '', 0, 2) . '"
			        }, "google_translate_element' . $id . '");
			    }
			');
		}
	}
}
