<?php declare(strict_types=1);

/**
 * CreditArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, Lang, Theme, User, Utils};
use Bugo\LightPortal\Helper;
use Nette\Utils\Html;

if (! defined('SMF'))
	die('No direct access...');

/**
 * See ?action=credits;sa=light_portal
 */
final class CreditArea
{
	use Helper;

	public function __invoke(): void
	{
		$this->applyHook('integrate_credits', 'show');
	}

	public function show(): void
	{
		Utils::$context['credits_modifications'][] = $this->getLink();

		if (Utils::$context['current_subaction'] && Utils::$context['current_subaction'] === 'light_portal') {
			$this->prepareComponents();

			Utils::$context['robot_no_index'] = true;

			Utils::$context['page_title'] = LP_NAME . ' - ' . Lang::$txt['lp_used_components'];

			Theme::loadTemplate('LightPortal/ViewCredits');

			Utils::$context['sub_template'] = 'portal_credits';

			Utils::obExit();
		}
	}

	public function getLink(): string
	{
		$link = Lang::$txt['lang_dictionary'] === 'ru'
			? 'https://dragomano.ru/mods/light-portal'
			: 'https://custom.simplemachines.org/mods/index.php?mod=4244';

		$license = Lang::$txt['credits_license'] . ': ' . Html::el('a', [
			'href'   => 'https://github.com/dragomano/Light-Portal/blob/master/LICENSE',
			'target' => '_blank',
			'rel'    => 'noopener',
		])->setText('GNU GPLv3')->toHtml();

		return Html::el('a', [
			'href'   => $link,
			'target' => '_blank',
			'rel'    => 'noopener',
			'title'  => LP_VERSION,
		])->setText(LP_NAME)->toHtml() . ' | &copy; ' . Html::el('a', [
			'href' => Config::$scripturl . '?action=credits;sa=light_portal',
		])->setHtml('2019&ndash;' . date('Y'))->toHtml() . ', Bugo | ' . $license;
	}

	public function prepareComponents(): void
	{
		User::mustHavePermission('light_portal_view');

		Utils::$context['portal_translations'] = [
			'Polish'     => ['Adrek', 'jsqx'],
			'Spanish'    => ['Rock Lee', 'Diego Andrés'],
			'French'     => ['Papoune57'],
			'Turkish'    => ['gevv'],
			'Ukrainian'  => ['valciriya1986'],
			'German'     => ['trucker2006', 'm4z'],
			'Italian'    => ['Darknico'],
			'Portuguese' => ['Costa'],
			'Greek'      => ['Panoulis64'],
		];

		Utils::$context['consultants'] = [
			[
				'name' => 'Tyrsson',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=155269',
			]
		];

		Utils::$context['testers'] = [
			[
				'name' => 'Wylek',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=608635'
			],
			[
				'name' => 'Panoulis64',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=301719'
			]
		];

		Utils::$context['sponsors'] = [
			[
				'name' => 'vbgamer45 <span class="amt">$50</span>',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=24876'
			],
		];

		Utils::$context['tools'] = [
			[
				'name' => 'Open Server Panel',
				'link' => 'https://github.com/OSPanel/OpenServerPanel'
			],
			[
				'name' => 'Visual Studio Code',
				'link' => 'https://code.visualstudio.com/Download'
			],
			[
				'name' => 'Unreal Commander',
				'link' => 'https://x-diesel.com'
			],
			[
				'name' => 'JetBrains',
				'link' => 'https://www.jetbrains.com/?from=LightPortal'
			],
			[
				'name' => 'Crowdin',
				'link' => 'https://crowdin.com/project/light-portal'
			],
			[
				'name' => 'Git Extensions',
				'link' => 'https://github.com/gitextensions/gitextensions'
			],
			[
				'name' => 'Firefox Developer Edition',
				'link' => 'https://www.mozilla.org/firefox/developer/'
			],
			[
				'name' => 'VitePress',
				'link' => 'https://vitepress.dev'
			],
			[
				'name' => 'Vite',
				'link' => 'https://vitejs.dev'
			],
		];

		$links = [
			[
				'title' => 'Flexbox Grid',
				'link' => 'https://github.com/evgenyrodionov/flexboxgrid2',
				'author' => 'Kristofer Joseph',
				'license' => [
					'name' => 'the Apache License 2.0',
					'link' => 'https://github.com/evgenyrodionov/flexboxgrid2/blob/master/LICENSE'
				]
			],
			[
				'title' => 'Font Awesome Free',
				'link' => 'https://fontawesome.com',
				'author' => 'Fonticons, Inc.',
				'license' => [
					'name' => 'the Font Awesome Free License',
					'link' => 'https://github.com/FortAwesome/Font-Awesome/blob/master/LICENSE.txt'
				]
			],
			[
				'title' => 'Alpine.js',
				'link' => 'https://github.com/alpinejs/alpine',
				'author' => 'Caleb Porzio and contributors',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/alpinejs/alpine/blob/master/LICENSE.md'
				]
			],
			[
				'title' => 'Vue.js',
				'link' => 'https://github.com/vuejs/core',
				'author' => 'Yuxi (Evan) You',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/vuejs/core/blob/main/LICENSE'
				]
			],
			[
				'title' => 'vue3-sfc-loader',
				'link' => 'https://github.com/FranckFreiburger/vue3-sfc-loader',
				'author' => 'Franck Freiburger',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/vuejs/core/blob/main/LICENSE'
				]
			],
			[
				'title' => 'Pinia',
				'link' => 'https://github.com/vuejs/pinia',
				'author' => 'Eduardo San Martin Morote',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/vuejs/pinia/blob/v2/LICENSE'
				]
			],
			[
				'title' => 'VueUse',
				'link' => 'https://github.com/vueuse/vueuse',
				'author' => 'Anthony Fu',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/vueuse/vueuse/blob/main/LICENSE'
				]
			],
			[
				'title' => 'Vue Showdown',
				'link' => 'https://github.com/meteorlxy/vue-showdown/',
				'author' => 'meteorlxy & contributors',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/meteorlxy/vue-showdown/blob/main/LICENSE'
				]
			],
			[
				'title' => 'Vue 3 Multiselect',
				'link' => 'https://github.com/vueform/multiselect',
				'author' => 'Adam Berecz',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/vueform/multiselect/blob/main/LICENSE.md'
				]
			],
			[
				'title' => 'Vue 3 Toggle',
				'link' => 'https://github.com/vueform/toggle',
				'author' => 'Adam Berecz',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/vueform/toggle/blob/main/LICENSE.md'
				]
			],
			[
				'title' => 'vue-i18n-next',
				'link' => 'https://github.com/intlify/vue-i18n-next/',
				'author' => 'kazuya kawaguchi',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/intlify/vue-i18n-next/blob/master/LICENSE'
				]
			],
			[
				'title' => '&lt;markdown-toolbar&gt; element',
				'link' => 'https://github.com/github/markdown-toolbar-element',
				'author' => 'GitHub, Inc.',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/github/markdown-toolbar-element/blob/main/LICENSE'
				]
			],
			[
				'title' => 'BladeOne Blade Template Engine',
				'link' => 'https://github.com/EFTEC/BladeOne',
				'author' => 'Jorge Patricio Castro Castillo',
				'license' => [
					'name' => 'The MIT License',
					'link' => 'https://github.com/EFTEC/BladeOne/blob/master/LICENSE'
				]
			],
			[
				'title' => 'Sortable.js',
				'link' => 'https://github.com/SortableJS/Sortable',
				'author' => 'All contributors to Sortable',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/SortableJS/Sortable/blob/master/LICENSE'
				]
			],
			[
				'title' => 'Alpine JS Slug',
				'link' => 'https://github.com/markmead/alpinejs-slug',
				'author' => 'Mark Mead',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/markmead/alpinejs-slug/blob/main/LICENSE'
				]
			],
			[
				'title' => 'Axios',
				'link' => 'https://github.com/axios/axios',
				'author' => 'Matt Zabriskie & Collaborators',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/axios/axios/blob/v1.x/LICENSE'
				]
			],
			[
				'title' => 'Virtual Select',
				'link' => 'https://sa-si-dev.github.io/virtual-select/',
				'author' => 'Sa Si Dev',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/sa-si-dev/virtual-select/blob/master/LICENSE'
				]
			],
			[
				'title' => 'LazyLoad',
				'link' => 'https://github.com/verlok/vanilla-lazyload',
				'author' => 'Andrea Verlicchi',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/verlok/vanilla-lazyload/blob/master/LICENSE'
				]
			]
		];

		// Adding copyrights of used plugins
		$this->hook('credits', [&$links]);

		Utils::$context['lp_components'] = $links;

		shuffle(Utils::$context['lp_components']);
	}
}
