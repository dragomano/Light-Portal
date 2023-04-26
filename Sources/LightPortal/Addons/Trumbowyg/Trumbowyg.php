<?php

/**
 * Trumbowyg.php
 *
 * @package Trumbowyg (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 25.04.23
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
		$config_vars['trumbowyg'][] = ['text', 'client_id', 'subtext' => sprintf($this->txt['lp_trumbowyg']['client_id_subtext'], 'https://api.imgur.com/oauth2/addclient')];
	}

	public function prepareEditor(array $object)
	{
		if ($object['type'] !== 'html' && (empty($object['options']['content']) || $object['options']['content'] !== 'html'))
			return;

		$dark_themes = $this->jsonDecode($this->context['lp_trumbowyg_plugin']['dark_themes'] ?? '', true);
		$auto_grow = empty($this->context['lp_trumbowyg_plugin']['auto_grow']) ? 0 : (int) $this->context['lp_trumbowyg_plugin']['auto_grow'];

		$this->loadLanguage('Editor');

		$this->addInlineCss('
		.trumbowyg-editor {
			height: 300px;
		}');

		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/ui/trumbowyg.min.css');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/trumbowyg.min.js');

		if ($this->txt['lang_dictionary'] !== 'en')
			$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/langs/' . $this->txt['lang_dictionary'] . '.min.js');

		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/history/trumbowyg.history.min.js');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/pasteimage/trumbowyg.pasteimage.min.js');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/pasteembed/trumbowyg.pasteembed.min.js');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/preformatted/trumbowyg.preformatted.min.js');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/upload/trumbowyg.upload.min.js');
		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/plugins/table/ui/trumbowyg.table.min.css');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/table/trumbowyg.table.min.js');

		$this->addInlineJavaScript('
		$("#content").trumbowyg({
			lang: "' . $this->txt['lang_dictionary'] . '",
			btnsDef: {
				historyUndo: {
					title: "' . $this->editortxt['undo'] . '"
				},
				historyRedo: {
					title: "' . $this->editortxt['redo'] . '"
				},' . (empty($this->context['lp_trumbowyg_plugin']['client_id']) ? '' : '
				image: {
					dropdown: ["insertImage", "upload"],
					ico: "insertImage"
				}') . '
			},
			btns: [
				["historyUndo", "historyRedo"],
				["strong", "em", "del"],
				["p", "h4"],
				["superscript", "subscript"],
				["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
				["' . (empty($this->context['lp_trumbowyg_plugin']['client_id']) ? 'insertImage' : 'image') . '", "link"],
				["table", "preformatted", "blockquote"],
				["unorderedList", "orderedList"],
				["horizontalRule"],
				["viewHTML", "removeformat"],
				["fullscreen"]
			],' . (empty($this->context['lp_trumbowyg_plugin']['client_id']) ? '' : ('
			plugins: {
				upload: {
					serverPath: "https://api.imgur.com/3/image",
					fileFieldName: "image",
					headers: {
						"Authorization": "Client-ID ' . $this->context['lp_trumbowyg_plugin']['client_id'] . '"
					},
					urlPropertyName: "data.link"
				}
			},')) . '
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
			$("#adm_submenus,#main_menu,#genericmenu,.noticebox,#gtb_pos").hide();
			$(".sticky_sidebar").css("position", "initial");
		}).on("tbwclosefullscreen", function() {
			$("#adm_submenus,#main_menu,#genericmenu,.noticebox,#gtb_pos").show();
			$(".sticky_sidebar").css("position", "sticky");
		});' . ($dark_themes && ! empty($dark_themes[$this->settings['theme_id']]) ? '
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
