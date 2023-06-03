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
 * @version 28.05.23
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
			autoDownloadFontAwesome: false,
			autofocus: true,
			spellChecker: false,
			placeholder: "' . $this->txt['lp_post_error_no_content'] . '",
			forceSync: true,
			direction: "' . ($this->context['right_to_left'] ? 'rtl' : 'ltr') . '",
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
					className: "fas fa-image",
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
					name: "guide",
					action: "https://github.com/dragomano/Light-Portal/wiki/Markdown-addon",
					className: "fas fa-question-circle",
					title: "' . $this->txt['lp_easy_markdown_editor']['guide'] . '"
				}
			]
		});', true);
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