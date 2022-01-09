<?php

/**
 * Trumbowyg.php
 *
 * @package Trumbowyg (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 05.01.22
 */

namespace Bugo\LightPortal\Addons\Trumbowyg;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Trumbowyg extends Plugin
{
	public string $type = 'editor';

	public function addSettings(array &$config_vars)
	{
		$config_vars['trumbowyg'][] = ['multicheck', 'dark_themes', $this->getForumThemes()];
		$config_vars['trumbowyg'][] = ['select', 'auto_grow', $this->txt['lp_trumbowyg']['auto_grow_set']];
	}

	public function prepareEditor(array $object)
	{
		if ($object['type'] !== 'html' && (empty($object['options']['content']) || $object['options']['content'] !== 'html'))
			return;

		$dark_themes = empty($this->modSettings['lp_trumbowyg_addon_dark_themes']) ? [] : json_decode($this->modSettings['lp_trumbowyg_addon_dark_themes'], true);
		$auto_grow = empty($this->modSettings['lp_trumbowyg_addon_auto_grow']) ? 0 : (int) $this->modSettings['lp_trumbowyg_addon_auto_grow'];

		loadLanguage('Editor');

		loadCSSFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/ui/trumbowyg.min.css', ['external' => true]);
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/trumbowyg.min.js', ['external' => true]);

		if ($this->txt['lang_dictionary'] !== 'en')
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/langs/' . $this->txt['lang_dictionary'] . '.min.js', ['external' => true]);

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/history/trumbowyg.history.min.js', ['external' => true]);
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/pasteimage/trumbowyg.pasteimage.min.js', ['external' => true]);
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/preformatted/trumbowyg.preformatted.min.js', ['external' => true]);
		loadCSSFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/plugins/table/ui/trumbowyg.table.min.css', ['external' => true]);
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/table/trumbowyg.table.min.js', ['external' => true]);
		addInlineJavaScript('
		$("#content").trumbowyg({
			lang: "' . $this->txt['lang_dictionary'] . '",
			btnsDef: {
				historyUndo: {
					title: "' . $this->editortxt['undo'] . '"
				},
				historyRedo: {
					title: "' . $this->editortxt['redo'] . '"
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
			imageWidthModalEdit: true' . ($auto_grow === 1 ? ',
			autogrow: true' : '') . ($auto_grow === 2 ? ',
			autogrowOnEnter: true' : '') . '
		}).on("tbwopenfullscreen", function() {
			$("#main_menu,#genericmenu,.noticebox,#gtb_pos").hide();
			$(".sticky_sidebar").css("position", "initial");
		}).on("tbwclosefullscreen", function() {
			$("#main_menu,#genericmenu,.noticebox,#gtb_pos").show();
			$(".sticky_sidebar").css("position", "sticky");
		});' . ($dark_themes && $dark_themes[$this->settings['theme_id']] ? '
		$(".pf_content").addClass("trumbowyg-dark");' : ''), true);
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Trumbowyg',
			'link' => 'https://github.com/Alex-D/Trumbowyg',
			'author' => 'Alexandre Demode (Alex-D)',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/Alex-D/Trumbowyg/blob/develop/LICENSE'
			]
		];
	}
}
