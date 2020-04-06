<?php

namespace Bugo\LightPortal\Addons;

use Bugo\LightPortal\Helpers;

/**
 * Trumbowyg
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

class Trumbowyg
{
	/**
	 * Adding your own editor for 'html' content
	 *
	 * Добавляем свой редактор для контента 'html'
	 *
	 * @param array $object
	 * @return void
	 */
	public static function prepareEditor($object)
	{
		global $txt, $editortxt, $settings;

		if ($object['type'] == 'html' || (!empty($object['options']['content']) && $object['options']['content'] === 'html')) {
			loadLanguage('Editor');

			loadCssFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/ui/trumbowyg.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/trumbowyg.min.js', array('external' => true));

			if ($txt['lang_dictionary'] !== 'en')
				loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/langs/' . $txt['lang_dictionary'] . '.min.js', array('external' => true));

			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/history/trumbowyg.history.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/pasteimage/trumbowyg.pasteimage.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/preformatted/trumbowyg.preformatted.min.js', array('external' => true));
			loadCssFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/plugins/table/ui/trumbowyg.table.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/table/trumbowyg.table.min.js', array('external' => true));
			addInlineJavaScript('
		$("#content").trumbowyg({
			lang: "' . $txt['lang_dictionary'] . '",
			btnsDef: {
				historyUndo: {
					title: "' . $editortxt['undo'] . '"
				},
				historyRedo: {
					title: "' . $editortxt['redo'] . '"
				}
			},
			btns: [
				["historyUndo", "historyRedo"],
				["strong", "em", "del"],
				["p", "h4"],
				["superscript", "subscript"],
				["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
				["insertImage", "link"],
				["table", "preformatted", "blockquote"],
				["unorderedList", "orderedList"],
				["horizontalRule"],
				["viewHTML", "removeformat"],
				["fullscreen"]
			],
			semantic: true,
			urlProtocol: true,
			resetCss: true,
			urlProtocol: true,
			removeformatPasted: true,
			imageWidthModalEdit: true
		});' . (Helpers::doesCurrentThemeContainFontAwesome()  ? '
		$(".pf_content").addClass("trumbowyg-dark");' : ''), true);
		}
	}

	/**
	 * Adding the addon copyright
	 *
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
