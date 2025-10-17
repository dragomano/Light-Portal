<?php declare(strict_types=1);

/**
 * @package EasyMarkdownEditor (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\EasyMarkdownEditor;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Editor;

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
			placeholder: "' . Lang::$txt['lp_post_error_no_content'] . '",
			forceSync: true,
			direction: "' . (Utils::$context['right_to_left'] ? 'rtl' : 'ltr') . '",
			toolbar: [
				{
					name: "bold",
					action: EasyMDE.toggleBold,
					className: "fas fa-bold",
					title: "' . Lang::$editortxt['bold'] . '"
				},
				{
					name: "italic",
					action: EasyMDE.toggleItalic,
					className: "fas fa-italic",
					title: "' . Lang::$editortxt['italic'] . '"
				},
				{
					name: "strikethrough",
					action: EasyMDE.toggleStrikethrough,
					className: "fas fa-strikethrough",
					title: "' . Lang::$editortxt['strikethrough'] . '"
				},
				"|",
				{
					name: "heading-4",
					action: (editor) => {
						EasyMDE.toggleHeading3(editor);
						EasyMDE.toggleHeadingSmaller(editor);
					},
					className: "fas fa-header",
					title: "' . Lang::$txt['lp_title'] . '"
				},
				"|",
				{
					name: "image",
					action: EasyMDE.drawImage,
					className: "fas fa-image",
					title: "' . Lang::$editortxt['insert_image'] . '"
				},
				{
					name: "link",
					action: EasyMDE.drawLink,
					className: "fas fa-link",
					title: "' . Lang::$editortxt['insert_link'] . '"
				},
				"|",
				{
					name: "table",
					action: EasyMDE.drawTable,
					className: "fas fa-table",
					title: "' . Lang::$editortxt['insert_table'] . '"
				},
				{
					name: "code",
					action: EasyMDE.toggleCodeBlock,
					className: "fas fa-code",
					title: "' . Lang::$editortxt['code'] . '"
				},
				{
					name: "quote",
					action: EasyMDE.toggleBlockquote,
					className: "fas fa-quote-left",
					title: "' . Lang::$editortxt['insert_quote'] . '"
				},
				"|",
				{
					name: "unordered-list",
					action: EasyMDE.toggleUnorderedList,
					className: "fas fa-list-ul",
					title: "' . Lang::$editortxt['bullet_list'] . '"
				},
				{
					name: "ordered-list",
					action: EasyMDE.toggleOrderedList,
					className: "fas fa-list-ol",
					title: "' . Lang::$editortxt['numbered_list'] . '"
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
					title: "' . Lang::$editortxt['insert_horizontal_rule'] . '"
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
