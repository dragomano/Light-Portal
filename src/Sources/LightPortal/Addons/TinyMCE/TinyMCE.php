<?php

/**
 * TinyMCE.php
 *
 * @package TinyMCE (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 10.02.24
 */

namespace Bugo\LightPortal\Addons\TinyMCE;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class TinyMCE extends Plugin
{
	public string $type = 'editor';

	public function addSettings(array &$settings): void
	{
		$link = '<a href="https://www.tiny.cloud/auth/signup/" class="bbc_link" target="_blank">' . Lang::$txt['lp_tiny_m_c_e']['api_key_subtext'] . '<a>';

		$settings['tiny_m_c_e'][] = ['text', 'api_key', 'subtext' => $link];
		$settings['tiny_m_c_e'][] = ['multiselect', 'dark_themes', $this->getForumThemes()];
	}

	public function prepareEditor(array $object): void
	{
		if ($object['type'] !== 'html' && (! isset($object['options']['content']) || $object['options']['content'] !== 'html'))
			return;

		$apiKey = Utils::$context['lp_tiny_m_c_e_plugin']['api_key'] ?? 'no-api-key';

		$this->loadExtJS('https://cdn.tiny.cloud/1/' . $apiKey . '/tinymce/6/tinymce.min.js', ['attributes' => ['referrerpolicy' => 'origin']]);

		$this->addInlineJS('
		const useDarkMode = ' . ($this->isDarkTheme(Utils::$context['lp_tiny_m_c_e_plugin']['dark_themes']) ? 'true' : 'false') . ';
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
				favs: { title: "' . Lang::$txt['lp_tiny_m_c_e']['favorites'] . '", items: "code visualaid | searchreplace | emoticons" }
			},
			menubar: "favs file edit view insert format tools table help",
			skin: useDarkMode ? "oxide-dark" : "oxide",
			content_css: useDarkMode ? "dark" : "default",
		});', true);
	}

	public function credits(array &$links): void
	{
		$links[] = [
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
