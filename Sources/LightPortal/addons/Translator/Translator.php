<?php

/**
 * Translator
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\Translator;

use Bugo\LightPortal\Addons\Plugin;

class Translator extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-language';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['translator']['no_content_class'] = true;

		$options['translator']['parameters'] = [
			'engine'       => 'google',
			'widget_theme' => 'light',
			'auto_mode'    => false,
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'translator')
			return;

		$parameters['engine']       = FILTER_SANITIZE_STRING;
		$parameters['widget_theme'] = FILTER_SANITIZE_STRING;
		$parameters['auto_mode']    = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'translator')
			return;

		$context['posting_fields']['engine']['label']['text'] = $txt['lp_translator']['engine'];
		$context['posting_fields']['engine']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'engine'
			),
			'options' => array()
		);

		$engines = array_combine(array('google', 'yandex'), $txt['lp_translator']['engine_set']);

		foreach ($engines as $key => $value) {
			$context['posting_fields']['engine']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['engine']
			);
		}

		if ($context['lp_block']['options']['parameters']['engine'] == 'google')
			return;

		$context['posting_fields']['widget_theme']['label']['text'] = $txt['lp_translator']['widget_theme'];
		$context['posting_fields']['widget_theme']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'widget_theme'
			)
		);

		$context['posting_fields']['widget_theme']['input']['options'] = array(
			'light' => array(
				'value'    => 'light',
				'selected' => 'light' == $context['lp_block']['options']['parameters']['widget_theme']
			),
			'dark' => array(
				'value'    => 'dark',
				'selected' => 'dark' == $context['lp_block']['options']['parameters']['widget_theme']
			)
		);

		$context['posting_fields']['auto_mode']['label']['text'] = $txt['lp_translator']['auto_mode'];
		$context['posting_fields']['auto_mode']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'auto_mode',
				'checked' => !empty($context['lp_block']['options']['parameters']['auto_mode'])
			)
		);
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $language;

		if ($type !== 'translator')
			return;

		ob_start();

		if ($parameters['engine'] == 'yandex') {
			echo '
		<div id="ytWidget', $block_id, '" class="centertext noup"></div>
		<script src="https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget', $block_id, '&amp;pageLang=', substr($language, 0, 2), '&amp;widgetTheme=', $parameters['widget_theme'], '&amp;autoMode=', (bool) $parameters['auto_mode'], '"></script>';
		} else {
			echo '
		<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		<div class="centertext noup">
			<div id="google_translate_element', $block_id, '"></div>
			<script>
				function googleTranslateElementInit() {
					new google.translate.TranslateElement({
						pageLanguage: "', substr($language, 0, 2), '"
					}, "google_translate_element', $block_id, '");
				}
			</script>
		</div>';
		}

		$content = ob_get_clean();
	}
}
