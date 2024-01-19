<?php

/**
 * CustomTranslate.php
 *
 * @package CustomTranslate (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.01.24
 */

namespace Bugo\LightPortal\Addons\CustomTranslate;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\{Config, Lang, Theme, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

class CustomTranslate extends Plugin
{
	public string $type = 'other';

	private array $langCodes = ['ar', 'de', 'el', 'en', 'eo', 'es', 'fr', 'hi', 'it', 'nl', 'pt', 'ru', 'sv', 'tr', 'uk', 'zh'];

	private array $langTitles = ['عربي', 'Deutsch', 'Ελληνικά', 'English', 'Esperanto', 'Español', 'Français', 'हिन्दी', 'Italiano', 'Nederlands', 'Português', 'Русский', 'Svenska', 'Türkçe', 'Українська', '中文 (简体)'];

	public function init(): void
	{
		if (isset(Utils::$context['uninstalling']) || $this->request()->has('xml') || empty(Utils::$context['lp_custom_translate_plugin']['languages']))
			return;

		if (Utils::$context['browser']['is_mobile'] || Utils::$context['browser']['possibly_robot'] || Utils::$context['current_action'] === 'helpadmin')
			return;

		if (in_array(Utils::$context['current_action'], ['jsmodify', 'quotefast', 'xmlhttp']) || Utils::$context['current_subaction'] === 'showoperations')
			return;

		$forumLang = substr(Config::$language, 0, 2);

		Theme::addInlineJS('new YandexTranslate({baseLang: "' . $forumLang . '"});', true);

		Utils::$context['ctw_languages'] = array_unique(array_merge([$forumLang], explode(',', Utils::$context['lp_custom_translate_plugin']['languages'])));

		Utils::$context['ctw_lang_titles'] = array_combine($this->langCodes, $this->langTitles);

		$this->setTemplate()->withLayer('custom_translate');
	}

	public function addSettings(array &$config_vars): void
	{
		$config_vars['custom_translate'][] = ['multiselect', 'languages', array_combine($this->langCodes, Lang::$txt['lp_custom_translate']['languages_set'])];
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'Yandex Translate Custom Widget',
			'link' => 'https://github.com/get-web/yandex-translate-custom-widget',
			'author' => 'Vitalii P. (get-web)',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/get-web/yandex-translate-custom-widget/blob/main/LICENSE'
			]
		];
	}
}
