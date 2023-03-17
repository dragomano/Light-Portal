<?php

/**
 * EasyMarkdownEditor.php
 *
 * @package EasyMarkdownEditor (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 4.03.23
 */

namespace Bugo\LightPortal\Addons\EasyMarkdownEditor;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class EasyMarkdownEditor extends Plugin
{
	public string $type = 'editor';

	public function prepareEditor(array $object)
	{
		if ($object['type'] !== 'markdown')
			return;

		$this->loadLanguage('Editor');

		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css');

		$this->addInlineCss('
		.editor-toolbar button {
			box-shadow: none;
		}
		.editor-statusbar .lines:before {
			content: "' . $this->txt['lp_easy_markdown_editor']['lines'] . '"
		}
		.editor-statusbar .words:before {
			content: "' . $this->txt['lp_easy_markdown_editor']['words'] . '"
		}
		.CodeMirror pre {
			max-height: none;
		}');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.js');
		$this->addInlineJavaScript('
		let easymde = new EasyMDE({
			element: document.getElementById("content"),
			autofocus: true,
			spellChecker: false,
			placeholder: "' . $this->txt['lp_post_error_no_content'] . '",
			forceSync: true,
			onToggleFullScreen: function () {
				toggleStickedPanels();
			},
			toolbar: [
				{
					name: "bold",
					action: EasyMDE.toggleBold,
					className: "fas fa-bold",
					title: "' . $this->editortxt['bold'] . '"
				},
				{
					name: "italic",
					action: EasyMDE.toggleItalic,
					className: "fas fa-italic",
					title: "' . $this->editortxt['italic'] . '"
				},
				{
					name: "strikethrough",
					action: EasyMDE.toggleStrikethrough,
					className: "fas fa-strikethrough",
					title: "' . $this->editortxt['strikethrough'] . '"
				},
				"|",
				{
					name: "heading-4",
					action: (editor) => {
						EasyMDE.toggleHeading3(editor);
						EasyMDE.toggleHeadingSmaller(editor);
					},
					className: "fas fa-header",
					title: "' . $this->txt['lp_title'] . '"
				},
				"|",
				{
					name: "image",
					action: EasyMDE.drawImage,
					className: "fas fa-picture-o",
					title: "' . $this->editortxt['insert_image'] . '"
				},
				{
					name: "link",
					action: EasyMDE.drawLink,
					className: "fas fa-link",
					title: "' . $this->editortxt['insert_link'] . '"
				},
				"|",
				{
					name: "table",
					action: EasyMDE.drawTable,
					className: "fas fa-table",
					title: "' . $this->editortxt['insert_table'] . '"
				},
				{
					name: "code",
					action: EasyMDE.toggleCodeBlock,
					className: "fas fa-code",
					title: "' . $this->editortxt['code'] . '"
				},
				{
					name: "quote",
					action: EasyMDE.toggleBlockquote,
					className: "fas fa-quote-left",
					title: "' . $this->editortxt['insert_quote'] . '"
				},
				"|",
				{
					name: "unordered-list",
					action: EasyMDE.toggleUnorderedList,
					className: "fas fa-list-ul",
					title: "' . $this->editortxt['bullet_list'] . '"
				},
				{
					name: "ordered-list",
					action: EasyMDE.toggleOrderedList,
					className: "fas fa-list-ol",
					title: "' . $this->editortxt['numbered_list'] . '"
				},
				{
					name: "task-list",
					action: (editor) => {
						editor.codemirror.replaceSelection(\'- [ ] \');
						editor.codemirror.focus();
					},
					className: "fas fa-tasks",
					title: "' . $this->txt['lp_easy_markdown_editor']['tasks'] . '"
				},
				{
					name: "horizontal-rule",
					action: EasyMDE.drawHorizontalRule,
					className: "fas fa-minus",
					title: "' . $this->editortxt['insert_horizontal_rule'] . '"
				},
				"|",
				{
					name: "preview",
					action: EasyMDE.togglePreview,
					className: "fas fa-eye no-disable",
					title: "' . $this->txt['preview'] . '"
				},
				{
					name: "side-by-side",
					action: EasyMDE.toggleSideBySide,
					className: "fas fa-columns no-disable no-mobile",
					title: "' . $this->txt['lp_easy_markdown_editor']['toggle'] . '"
				},
				{
					name: "fullscreen",
					action: EasyMDE.toggleFullScreen,
					className: "fas fa-arrows-alt no-disable no-mobile",
					title: "' . $this->editortxt['maximize'] . '"
				},
				"|",
				{
					name: "guide",
					action: "https://github.com/dragomano/Light-Portal/wiki/Markdown-addon",
					className: "fas fa-question-circle",
					title: "' . $this->txt['lp_easy_markdown_editor']['guide'] . '"
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
				if (scrollingButtons)
					scrollingButtons.style.display = "none";
			} else {
				stickedPanels.forEach(function (el) {
					el.style.position = "sticky";
				})
				noticeBlocks.forEach(function (el) {
					el.style.display = "block";
				})
				if (scrollingButtons)
					scrollingButtons.style.display = "block";
			}
		}', true);
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'EasyMDE',
			'link' => 'https://github.com/Ionaru/easy-markdown-editor',
			'author' => 'Sparksuite, Inc. | Jeroen Akkerman',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/Ionaru/easy-markdown-editor/blob/master/LICENSE'
			]
		];
	}
}
