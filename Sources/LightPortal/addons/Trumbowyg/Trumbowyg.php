<?php

/**
 * Trumbowyg.php
 *
 * @package Trumbowyg (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\Trumbowyg;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class Trumbowyg extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'editor';

	/**
	 * @var array
	 */
	public $disables = ['Jodit'];

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(array &$config_vars)
	{
		global $txt;

		$config_vars['trumbowyg'][] = array('multicheck', 'dark_themes', Helpers::getForumThemes());
		$config_vars['trumbowyg'][] = array('select', 'auto_grow', $txt['lp_trumbowyg']['auto_grow_set']);
	}

	/**
	 * Adding your own editor for 'html' content
	 *
	 * Добавляем свой редактор для контента 'html'
	 *
	 * @param array $object
	 * @return void
	 */
	public function prepareEditor(array $object)
	{
		global $modSettings, $txt, $editortxt, $settings;

		if ($object['type'] == 'html' || (!empty($object['options']['content']) && $object['options']['content'] === 'html')) {
			$dark_themes = !empty($modSettings['lp_trumbowyg_addon_dark_themes']) ? json_decode($modSettings['lp_trumbowyg_addon_dark_themes'], true) : [];

			loadLanguage('Editor');

			loadCSSFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/ui/trumbowyg.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/trumbowyg.min.js', array('external' => true));

			if ($txt['lang_dictionary'] !== 'en')
				loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/langs/' . $txt['lang_dictionary'] . '.min.js', array('external' => true));

			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/history/trumbowyg.history.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/pasteimage/trumbowyg.pasteimage.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/preformatted/trumbowyg.preformatted.min.js', array('external' => true));
			loadCSSFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/plugins/table/ui/trumbowyg.table.min.css', array('external' => true));
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
			semantic: {
				"div": "div"
			},
			urlProtocol: true,
			resetCss: true,
			removeformatPasted: true,
			imageWidthModalEdit: true' . (!empty($modSettings['lp_trumbowyg_addon_auto_grow']) && $modSettings['lp_trumbowyg_addon_auto_grow'] == 1 ? ',
			autogrow: true' : '') . (!empty($modSettings['lp_trumbowyg_addon_auto_grow']) && $modSettings['lp_trumbowyg_addon_auto_grow'] == 2 ? ',
			autogrowOnEnter: true' : '') . '
		}).on("tbwopenfullscreen", function() {
			$("#main_menu,#genericmenu,.noticebox,#gtb_pos").hide();
			$(".sticky_sidebar").css("position", "initial");
		}).on("tbwclosefullscreen", function() {
			$("#main_menu,#genericmenu,.noticebox,#gtb_pos").show();
			$(".sticky_sidebar").css("position", "sticky");
		});' . (!empty($dark_themes) && !empty($dark_themes[$settings['theme_id']]) ? '
		$(".pf_content").addClass("trumbowyg-dark");' : ''), true);
		}
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(array &$links)
	{
		$links[] = array(
			'title' => 'Trumbowyg',
			'link' => 'https://github.com/Alex-D/Trumbowyg',
			'author' => 'Alexandre Demode (Alex-D)',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/Alex-D/Trumbowyg/blob/develop/LICENSE'
			)
		);
	}
}
