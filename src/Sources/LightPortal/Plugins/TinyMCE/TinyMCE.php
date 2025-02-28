<?php declare(strict_types=1);

/**
 * @package TinyMCE (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\TinyMCE;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasThemes;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class TinyMCE extends Plugin
{
	use HasThemes;

	public string $type = 'editor';

	public function addSettings(Event $e): void
	{
		$link = Str::html('a', [
			'class'  => 'bbc_link',
			'target' => '_blank',
			'href'   => 'https://www.tiny.cloud/auth/signup/',
		])->setText($this->txt['api_key_subtext']);

		$e->args->settings[$this->name][] = ['text', 'api_key', 'subtext' => $link->toHtml()];
		$e->args->settings[$this->name][] = ['multiselect', 'dark_themes', $this->getForumThemes()];
	}

	public function prepareEditor(Event $e): void
	{
		$object = $e->args->object;

		if ($object['type'] !== 'html' && (! isset($oobject['options']['content']) || $oobject['options']['content'] !== 'html'))
			return;

		$apiKey = $this->context['api_key'] ?? 'no-api-key';

		Theme::loadJavaScriptFile(
			'https://cdn.tiny.cloud/1/' . $apiKey . '/tinymce/6/tinymce.min.js',
			[
				'external' => true,
				'attributes' => [
					'referrerpolicy' => 'origin'
				]
			]
		);

		Theme::addInlineJavaScript('
		const useDarkMode = ' . ($this->isDarkTheme($this->context['dark_themes']) ? 'true' : 'false') . ';
		tinymce.init({
			selector: "#content",
			language: "' . Lang::$txt['lang_dictionary'] . '",
			directionality: "' . (Utils::$context['right_to_left'] ? 'rtl' : 'ltr') . '",
			plugins: [
				"advlist", "autolink", "link", "image", "lists", "charmap", "preview", "anchor", "pagebreak",
				"searchreplace", "wordcount", "visualblocks", "visualchars", "code",
				"media", "table", "emoticons", "help"
			],
			toolbar: "undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | " +
				"bullist numlist outdent indent | link image | preview media fullscreen | " +
				"forecolor backcolor emoticons | help",
			menu: {
				favs: { title: "' . $this->txt['favorites'] . '", items: "code visualaid | searchreplace | emoticons" }
			},
			menubar: "favs file edit view insert format tools table help",
			skin: useDarkMode ? "oxide-dark" : "oxide",
			content_css: useDarkMode ? "dark" : "default",
		});', true);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'TinyMCE',
			'link' => 'https://github.com/tinymce/tinymce',
			'author' => 'Ephox Corporation DBA Tiny Technologies, Inc.',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/tinymce/tinymce/blob/develop/LICENSE.TXT'
			]
		];
	}
}
