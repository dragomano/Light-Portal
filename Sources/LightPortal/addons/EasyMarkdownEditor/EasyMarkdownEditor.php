<?php

namespace Bugo\LightPortal\Addons\EasyMarkdownEditor;

/**
 * EasyMarkdownEditor
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class EasyMarkdownEditor
{
	/**
	 * @var array
	 */
	public $addon_type = 'editor';

	/**
	 * Adding the editor for 'md' content
	 *
	 * Подключаем редактор для контента 'md'
	 *
	 * @param array $object
	 * @return void
	 */
	public function prepareEditor($object)
	{
		global $txt, $editortxt;

		if ($object['type'] == 'md') {
			loadLanguage('Editor');

			loadCSSFile('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css', array('external' => true));
			addInlineCss('
		.editor-toolbar button {
			box-shadow: none;
		}
		.editor-statusbar .lines:before {
			content: "' . $txt['lp_easy_markdown_editor_addon_lines'] . '"
		}
		.editor-statusbar .words:before {
			content: "' . $txt['lp_easy_markdown_editor_addon_words'] . '"
		}
		.CodeMirror pre {
			max-height: none;
		}');
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.js', array('external' => true));
			addInlineJavaScript('
		let easymde = new EasyMDE({
			element: document.getElementById("content"),
			autofocus: true,
			spellChecker: false,
			placeholder: "' . $txt['lp_post_error_no_content'] . '",
			forceSync: true,
			onToggleFullScreen: function () {
				toggleStickedPanels();
			},
			toolbar: [
				{
					name: "bold",
					action: EasyMDE.toggleBold,
					className: "fas fa-bold",
					title: "' . $editortxt['bold'] . '"
				},
				{
					name: "italic",
					action: EasyMDE.toggleItalic,
					className: "fas fa-italic",
					title: "' . $editortxt['italic'] . '"
				},
				{
					name: "strikethrough",
					action: EasyMDE.toggleStrikethrough,
					className: "fas fa-strikethrough",
					title: "' . $editortxt['strikethrough'] . '"
				},
				"|",
				{
					name: "heading-4",
					action: (editor) => {
						EasyMDE.toggleHeading3(editor);
						EasyMDE.toggleHeadingSmaller(editor);
					},
					className: "fas fa-header",
					title: "' . $txt['lp_title'] . '"
				},
				"|",
				{
					name: "image",
					action: EasyMDE.drawImage,
					className: "fas fa-picture-o",
					title: "' . $editortxt['insert_image'] . '"
				},
				{
					name: "link",
					action: EasyMDE.drawLink,
					className: "fas fa-link",
					title: "' . $editortxt['insert_link'] . '"
				},
				"|",
				{
					name: "table",
					action: EasyMDE.drawTable,
					className: "fas fa-table",
					title: "' . $editortxt['insert_table'] . '"
				},
				{
					name: "code",
					action: EasyMDE.toggleCodeBlock,
					className: "fas fa-code",
					title: "' . $editortxt['code'] . '"
				},
				{
					name: "quote",
					action: EasyMDE.toggleBlockquote,
					className: "fas fa-quote-left",
					title: "' . $editortxt['insert_quote'] . '"
				},
				"|",
				{
					name: "unordered-list",
					action: EasyMDE.toggleUnorderedList,
					className: "fas fa-list-ul",
					title: "' . $editortxt['bullet_list'] . '"
				},
				{
					name: "ordered-list",
					action: EasyMDE.toggleOrderedList,
					className: "fas fa-list-ol",
					title: "' . $editortxt['numbered_list'] . '"
				},
				{
					name: "task-list",
					action: (editor) => {
						editor.codemirror.replaceSelection(\'- [ ] \');
						editor.codemirror.focus();
					},
					className: "fas fa-tasks",
					title: "' . $txt['lp_easy_markdown_editor_addon_tasks'] . '"
				},
				{
					name: "horizontal-rule",
					action: EasyMDE.drawHorizontalRule,
					className: "fas fa-minus",
					title: "' . $editortxt['insert_horizontal_rule'] . '"
				},
				"|",
				{
					name: "preview",
					action: EasyMDE.togglePreview,
					className: "fas fa-eye no-disable",
					title: "' . $txt['preview'] . '"
				},
				{
					name: "side-by-side",
					action: EasyMDE.toggleSideBySide,
					className: "fas fa-columns no-disable no-mobile",
					title: "' . $txt['lp_easy_markdown_editor_addon_toggle'] . '"
				},
				{
					name: "fullscreen",
					action: EasyMDE.toggleFullScreen,
					className: "fas fa-arrows-alt no-disable no-mobile",
					title: "' . $editortxt['maximize'] . '"
				},
				"|",
				{
					name: "guide",
					action: "https://github.com/dragomano/Light-Portal/wiki/Markdown-addon",
					className: "fas fa-question-circle",
					title: "' . $txt['lp_easy_markdown_editor_addon_guide'] . '"
				}
			]
		});

		function toggleStickedPanels() {
			let stickedPanels = document.getElementsByClassName("sticky_sidebar");
			let noticeBlocks = document.getElementsByClassName("noticebox");
			let scrollingButtons = document.getElementById("gtb_pos");

			if (!stickedPanels && !noticeBlocks && !scrollingButtons)
				return;

			if (easymde.isFullscreenActive()) {
				stickedPanels.forEach(function (el) {
					el.style.position = "initial";
				})
				noticeBlocks.forEach(function (el) {
					el.style.display = "none";
				})
				scrollingButtons.style.display = "none";
			} else {
				stickedPanels.forEach(function (el) {
					el.style.position = "sticky";
				})
				noticeBlocks.forEach(function (el) {
					el.style.display = "block";
				})
				scrollingButtons.style.display = "block";
			}
		}', true);
		}
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
	{
		$links[] = array(
			'title' => 'EasyMDE',
			'link' => 'https://github.com/Ionaru/easy-markdown-editor',
			'author' => 'Sparksuite, Inc. | Jeroen Akkerman',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/Ionaru/easy-markdown-editor/blob/master/LICENSE'
			)
		);
	}
}
