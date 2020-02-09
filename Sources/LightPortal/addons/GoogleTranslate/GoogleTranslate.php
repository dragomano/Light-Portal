<?php

namespace Bugo\LightPortal\Addons\GoogleTranslate;

/**
 * GoogleTranslate
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class GoogleTranslate
{
	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['google_translate'] = array(
			'no_content_class' => static::$no_content_class
		);
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public static function prepareContent(&$content, $type)
	{
		global $language;

		if ($type !== 'google_translate')
			return;

		ob_start();

		echo '
		<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		<div class="centertext noup">
			<div id="google_translate_element"></div>
			<script>
				function googleTranslateElementInit() {
					new google.translate.TranslateElement({
						pageLanguage: "', substr($language, 0, 2), '"
					}, "google_translate_element");
				}
			</script>
		</div>';

		$content = ob_get_clean();
	}
}
