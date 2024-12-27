<?php declare(strict_types=1);

/**
 * @package CustomTranslate (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\CustomTranslate;

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class CustomTranslate extends Plugin
{
	public string $type = 'other';

	private array $langCodes = [
		'ar', 'de', 'el', 'en',
		'eo', 'es', 'fr', 'hi',
		'it', 'nl', 'pt', 'ru',
		'sv', 'tr', 'uk', 'zh'
	];

	private array $langTitles = [
		'عربي', 'Deutsch', 'Ελληνικά', 'English',
		'Esperanto', 'Español', 'Français', 'हिन्दी',
		'Italiano', 'Nederlands', 'Português', 'Русский',
		'Svenska', 'Türkçe', 'Українська', '中文 (简体)'
	];

	public function init(): void
	{
		$this->applyHook(Hook::menuButtons);
	}

	public function menuButtons(): void
	{
		if (isset(Utils::$context['uninstalling']) || $this->request()->has('xml'))
			return;

		if (empty($this->context['languages']))
			return;

		if (Utils::$context['browser']['is_mobile'] || Utils::$context['browser']['possibly_robot'])
			return;

		if (Utils::$context['current_action'] === 'helpadmin')
			return;

		if (in_array(Utils::$context['current_action'], ['jsmodify', 'quotefast', 'xmlhttp']))
			return;

		if (Utils::$context['current_subaction'] === 'showoperations')
			return;

		$forumLang = substr(Config::$language ?? '', 0, 2);

		Theme::addInlineJavaScript('new YandexTranslate({baseLang: "' . $forumLang . '"});', true);

		Utils::$context['ctw_languages'] = array_unique(
			array_merge([$forumLang], explode(',', (string) $this->context['languages']))
		);

		Utils::$context['ctw_lang_titles'] = array_combine($this->langCodes, $this->langTitles);

		$this->setTemplate()->withLayer($this->name);
	}

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['multiselect', 'languages', array_combine(
			$this->langCodes, $this->txt['languages_set']
		)];
	}

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
}
