<?php

namespace Bugo\LightPortal\Addons\EasyMarkdownEditor;

/**
 * EasyMarkdownEditor
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class EasyMarkdownEditor
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var array
	 */
	public $addon_type = array('block', 'editor');

	/**
	 * Adding the new content type
	 *
	 * Добавляем новый тип контента
	 *
	 * @return void
	 */
	public function init()
	{
		global $txt;

		$txt['lp_page_types']['md'] = 'Markdown';
	}

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['md'] = array(
			'content' => true
		);
	}

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

			loadCssFile('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css', array('external' => true));
			addInlineCss('
		.editor-toolbar button {
			box-shadow: none;
		}
		.editor-statusbar .lines:before {
			content: "' . $txt['lp_easy_markdown_editor_addon_lines'] . '"
		}
		.editor-statusbar .words:before {
			content: "' . $txt['lp_easy_markdown_editor_addon_words'] . '"
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

			if (!stickedPanels)
				return;

			if (easymde.isFullscreenActive()) {
				stickedPanels.forEach(function (el) {
					el.style.position = "initial";
				})
			} else {
				stickedPanels.forEach(function (el) {
					el.style.position = "sticky";
				})
			}
		}', true);
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
