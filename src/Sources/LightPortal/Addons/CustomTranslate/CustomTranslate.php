<?php

/**
 * CustomTranslate.php
 *
 * @package CustomTranslate (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 10.11.23
 */

namespace Bugo\LightPortal\Addons\CustomTranslate;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class CustomTranslate extends Plugin
{
	public string $type = 'other';

	private array $langCodes = ['ar', 'de', 'el', 'en', 'eo', 'es', 'fr', 'hi', 'it', 'nl', 'pt', 'ru', 'sv', 'tr', 'uk', 'zh'];

	private array $langTitles = ['عربي', 'Deutsch', 'Ελληνικά', 'English', 'Esperanto', 'Español', 'Français', 'हिन्दी', 'Italiano', 'Nederlands', 'Português', 'Русский', 'Svenska', 'Türkçe', 'Українська', '中文 (简体)'];

	public function init(): void
	{
		if (isset($this->context['uninstalling']) || $this->request()->has('xml') || empty($this->context['lp_custom_translate_plugin']['languages']))
			return;

		if ($this->context['browser']['is_mobile'] || $this->context['browser']['possibly_robot'] || $this->context['current_action'] === 'helpadmin')
			return;

		if (in_array($this->context['current_action'], ['jsmodify', 'quotefast', 'xmlhttp']) || $this->context['current_subaction'] === 'showoperations')
			return;

		$forumLang = substr($this->language, 0, 2);

		$this->addInlineJavaScript('new YandexTranslate({baseLang: "' . $forumLang . '"});', true);

		$this->context['ctw_languages'] = array_unique(array_merge([$forumLang], explode(',', $this->context['lp_custom_translate_plugin']['languages'])));

		$this->context['ctw_lang_titles'] = array_combine($this->langCodes, $this->langTitles);

		$this->setTemplate()->withLayer('custom_translate');
	}

	public function addSettings(array &$config_vars): void
	{
		$config_vars['custom_translate'][] = ['select', 'languages', array_combine($this->langCodes, $this->txt['lp_custom_translate']['languages_set']), 'multiple' => true];
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
