<?php

/**
 * CustomTranslateWidget.php
 *
 * @package CustomTranslateWidget (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 12.04.23
 */

namespace Bugo\LightPortal\Addons\CustomTranslateWidget;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class CustomTranslateWidget extends Block
{
	public string $icon = 'fas fa-language';

	private array $langCodes = ['ar', 'de', 'el', 'en', 'eo', 'es', 'fr', 'hi', 'it', 'nl', 'pt', 'ru', 'sv', 'tr', 'uk', 'zh'];

	private array $langTitles = ['عربي', 'Deutsch', 'Ελληνικά', 'English', 'Esperanto', 'Español', 'Français', 'हिन्दी', 'Italiano', 'Nederlands', 'Português', 'Русский', 'Svenska', 'Türkçe', 'Українська', '中文 (简体)'];

	public function __construct()
	{
		$this->forumLang = substr($this->language, 0, 2);
	}

	public function blockOptions(array &$options)
	{
		$options['custom_translate_widget']['no_content_class'] = true;

		$options['custom_translate_widget']['parameters'] = [
			'languages' => '',
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'custom_translate_widget')
			return;

		$parameters['languages'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'custom_translate_widget')
			return;

		$this->context['lp_block_tab_appearance'] = false;

		$this->context['posting_fields']['title'] = ['no'];

		$this->context['custom_translate_widget_languages'] = array_combine($this->langCodes, $this->txt['lp_custom_translate_widget']['languages_set']);

		if (isset($this->context['custom_translate_widget_languages'][$this->forumLang]))
			unset($this->context['custom_translate_widget_languages'][$this->forumLang]);

		$this->context['posting_fields']['languages']['label']['html'] = '<label for="languages">' . $this->txt['lp_custom_translate_widget']['languages'] . '</label>';
		$this->context['posting_fields']['languages']['input']['html'] = (new LanguageSelect)();
		$this->context['posting_fields']['languages']['input']['tab']  = 'content';
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'custom_translate_widget' || isBrowser('is_mobile') || isBrowser('possibly_robot'))
			return;

		if (empty($parameters['languages']))
			return;

		$this->addInlineJavaScript('new YandexTranslate({baseLang: "' . $this->forumLang . '", id: "' . $block_id . '"});', true);

		$languages = array_unique(array_merge([$this->forumLang], explode(',', $parameters['languages'])));

		$lang_titles = array_combine($this->langCodes, $this->langTitles);

		echo '
		<div class="lang lang_fixed">
			<div id="ytWidget', $block_id, '" style="display: none"></div>
			<div class="lang__link lang__link_select" data-lang-active="">
				<div class="lang__code lang_', $this->txt['lang_dictionary'], '"></div>
			</div>
			<div class="lang__list" data-lang-list="" translate="no">';

		foreach ($languages as $lang) {
			echo '
				<a class="lang__link lang__link_sub" data-ya-lang="', $lang, '" title="', $lang_titles[$lang], '">
					<div class="lang__code lang_', $lang, '"></div>
				</a>';
		}

		echo '
			</div>
		</div>';
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'yandex-translate-custom-widget',
			'link' => 'https://github.com/get-web/yandex-translate-custom-widget',
			'author' => 'Vitalii P. (get-web)',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/get-web/yandex-translate-custom-widget/blob/main/LICENSE'
			]
		];
	}
}
