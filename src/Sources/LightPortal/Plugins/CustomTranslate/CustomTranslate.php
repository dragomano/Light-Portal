<?php declare(strict_types=1);

/**
 * @package CustomTranslate (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 30.09.25
 */

namespace Bugo\LightPortal\Plugins\CustomTranslate;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ForumHook;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute]
class CustomTranslate extends Plugin
{
	private array $langCodes = [
		'ar', 'de', 'el', 'en',
		'eo', 'es', 'fr', 'hi',
		'it', 'nl', 'pt', 'ru',
		'sv', 'tr', 'uk', 'zh',
	];

	private array $langTitles = [
		'عربي', 'Deutsch', 'Ελληνικά', 'English',
		'Esperanto', 'Español', 'Français', 'हिन्दी',
		'Italiano', 'Nederlands', 'Português', 'Русский',
		'Svenska', 'Türkçe', 'Українська', '中文 (简体)',
	];

	#[HookAttribute(PortalHook::init)]
	public function init(): void
	{
		$this->applyHook(ForumHook::menuButtons);
	}

	public function menuButtons(): void
	{
		if (! $this->canShowWidget())
			return;

		$userLang = substr(Utils::$context['user']['language'] ?? '', 0, 2);

		Theme::addInlineJavaScript('new YandexTranslate({baseLang: "' . $userLang . '"});', true);

		Utils::$context['ctw_languages'] = array_unique(
			array_merge([$userLang], explode(',', (string) $this->context['languages']))
		);

		Utils::$context['ctw_lang_titles'] = array_combine($this->langCodes, $this->langTitles);
	}

	#[HookAttribute(PortalHook::addLayerBelow)]
	public function addLayerBelow(): void
	{
		if (! $this->canShowWidget() || empty(Utils::$context['ctw_lang_titles']))
			return;

		$langLinks = Str::html('div', [
			'class'          => 'lang__list',
			'data-lang-list' => '',
			'translate'      => 'no',
		]);

		foreach (Utils::$context['ctw_languages'] as $lang) {
			$langLinks->addHtml(
				Str::html('a', [
					'class'        => 'lang__link lang__link_sub',
					'data-ya-lang' => $lang,
					'title'        => Utils::$context['ctw_lang_titles'][$lang],
				])->addHtml(
					Str::html('div')->class('lang__code lang_' . $lang)
				)
			);
		}

		echo Str::html('div')
			->class('lang lang_fixed')
			->addHtml(
				Str::html('div', [
					'id'    => 'ytWidget',
					'style' => 'display: none',
				])
			)
			->addHtml(
				Str::html('div', [
					'data-lang-active' => '',
				])
					->class('lang__link lang__link_select')
					->addHtml(
						Str::html('div')->class('lang__code lang_' . Lang::$txt['lang_dictionary'])
					)
			)
			->addHtml($langLinks);
	}

	#[HookAttribute(PortalHook::addSettings)]
	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['multiselect', 'languages', array_combine(
			$this->langCodes, $this->txt['languages_set']
		)];
	}

	#[HookAttribute(PortalHook::credits)]
	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Yandex Translate Custom Widget',
			'link' => 'https://github.com/get-web/yandex-translate-custom-widget',
			'author' => 'Vitalii P. (get-web)',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/get-web/yandex-translate-custom-widget/blob/main/LICENSE'
			]
		];
	}

	private function canShowWidget(): bool
	{
		return match (true) {
			isset(Utils::$context['uninstalling']) || $this->request()->has('xml') => false,
			empty($this->context['languages']) => false,
			Utils::$context['browser']['is_mobile'] || Utils::$context['browser']['possibly_robot'] => false,
			in_array(Utils::$context['current_action'], ['helpadmin', 'showoperations']) => false,
			in_array(Utils::$context['current_action'], ['jsmodify', 'quotefast', 'xmlhttp']) => false,
			default => true
		};
	}
}
