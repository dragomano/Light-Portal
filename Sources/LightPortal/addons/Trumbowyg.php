<?php

namespace Bugo\LightPortal\Addons;

/**
 * Trumbowyg
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Trumbowyg
{
	/**
	 * Добавляем свой редактор для контента 'html'
	 *
	 * @param array $object
	 * @return void
	 */
	public static function prepareEditor($object)
	{
		global $txt;

		if ($object['type'] == 'html') {
			loadCssFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/ui/trumbowyg.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/trumbowyg.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/langs/' . $txt['lang_dictionary'] . '.min.js', array('external' => true));
			addInlineJavaScript('
		$("#content").trumbowyg({
			lang: "' . $txt['lang_dictionary'] . '",
			semantic: true,
			imageWidthModalEdit: true
		});', true);
		}
	}

	/**
	 * Добавляем копирайты плагина
	 *
	 * @param array $links
	 * @return void
	 */
	public static function credits(&$links)
	{
		$links[] = array(
			'title' => 'Trumbowyg',
			'link' => 'https://github.com/Alex-D/Trumbowyg',
			'author' => '2012-2016 Alexandre Demode (Alex-D)',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/Alex-D/Trumbowyg/blob/develop/LICENSE'
			)
		);
	}
}
