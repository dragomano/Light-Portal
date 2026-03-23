<?php declare(strict_types=1);

/**
 * @package EasyMarkdownEditor (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 10.02.26
 */

namespace LightPortal\Plugins\EasyMarkdownEditor;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Editor;

if (! defined('LP_NAME'))
	die('No direct access...');

class EasyMarkdownEditor extends Editor
{
	public function prepareEditor(Event $e): void
	{
		if (! $this->isContentSupported($e->args->object))
			return;

		Lang::load('Editor');

		$this->loadExternalResources([
			['type' => 'css', 'url' => 'https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css'],
			['type' => 'js',  'url' => 'https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.js'],
		]);

		Theme::addInlineCss('
		.editor-toolbar button {
			box-shadow: none;
		}
		.editor-statusbar .lines:before {
			content: "' . $this->txt['lines'] . '"
		}
		.editor-statusbar .words:before {
			content: "' . $this->txt['words'] . '"
		}
		.CodeMirror pre {
			max-height: none;
		}');

		Theme::addInlineJavaScript('
		let easymde = new EasyMDE({
			element: document.getElementById("content"),
			autoDownloadFontAwesome: false,
			autofocus: true,
			spellChecker: false,
			placeholder: "' . __('lp_post_error_no_content') . '",
			forceSync: true,
			direction: "' . (Utils::$context['right_to_left'] ? 'rtl' : 'ltr') . '",
			toolbar: [
				{
					name: "bold",
					action: EasyMDE.toggleBold,
					className: "fas fa-bold",
					title: "' . __('bold', var: 'editortxt') . '"
				},
				{
					name: "italic",
					action: EasyMDE.toggleItalic,
					className: "fas fa-italic",
					title: "' . __('italic', var: 'editortxt') . '"
				},
				{
					name: "strikethrough",
					action: EasyMDE.toggleStrikethrough,
					className: "fas fa-strikethrough",
					title: "' . __('strikethrough', var: 'editortxt') . '"
				},
				"|",
				{
					name: "heading-4",
					action: (editor) => {
						EasyMDE.toggleHeading3(editor);
						EasyMDE.toggleHeadingSmaller(editor);
					},
					className: "fas fa-header",
					title: "' . __('lp_title') . '"
				},
				"|",
				{
					name: "image",
					action: EasyMDE.drawImage,
					className: "fas fa-image",
					title: "' . __('insert_image', var: 'editortxt') . '"
				},
				{
					name: "link",
					action: EasyMDE.drawLink,
					className: "fas fa-link",
					title: "' . __('insert_link', var: 'editortxt') . '"
				},
				"|",
				{
					name: "table",
					action: EasyMDE.drawTable,
					className: "fas fa-table",
					title: "' . __('insert_table', var: 'editortxt') . '"
				},
				{
					name: "code",
					action: EasyMDE.toggleCodeBlock,
					className: "fas fa-code",
					title: "' . __('code', var: 'editortxt') . '"
				},
				{
					name: "quote",
					action: EasyMDE.toggleBlockquote,
					className: "fas fa-quote-left",
					title: "' . __('insert_quote', var: 'editortxt') . '"
				},
				"|",
				{
					name: "unordered-list",
					action: EasyMDE.toggleUnorderedList,
					className: "fas fa-list-ul",
					title: "' . __('bullet_list', var: 'editortxt') . '"
				},
				{
					name: "ordered-list",
					action: EasyMDE.toggleOrderedList,
					className: "fas fa-list-ol",
					title: "' . __('numbered_list', var: 'editortxt') . '"
				},
				{
					name: "task-list",
					action: (editor) => {
						editor.codemirror.replaceSelection(\'- [ ] \');
						editor.codemirror.focus();
					},
					className: "fas fa-tasks",
					title: "' . $this->txt['tasks'] . '"
				},
				{
					name: "horizontal-rule",
					action: EasyMDE.drawHorizontalRule,
					className: "fas fa-minus",
					title: "' . __('insert_horizontal_rule', var: 'editortxt') . '"
				},
				"|",
				{
					name: "guide",
					action: "https://github.com/dragomano/Light-Portal/wiki/Markdown-addon",
					className: "fas fa-question-circle",
					title: "' . $this->txt['guide'] . '"
				}
			]
		});', true);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'EasyMDE',
			'link' => 'https://github.com/Ionaru/easy-markdown-editor',
			'author' => 'Sparksuite, Inc. | Jeroen Akkerman',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/Ionaru/easy-markdown-editor/blob/master/LICENSE'
			]
		];
	}

	protected function getSupportedContentTypes(): array
	{
		return ['markdown'];
	}
}
