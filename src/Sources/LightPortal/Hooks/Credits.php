<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\Str;

use const LP_NAME;
use const LP_VERSION;

class Credits extends AbstractHook
{
	public function __invoke(): void
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

	private function getLink(): string
	{
		$link = Lang::$txt['lang_dictionary'] === 'ru'
			? 'https://dragomano.ru/mods/light-portal'
			: 'https://custom.simplemachines.org/mods/index.php?mod=4244';

		$license = Lang::$txt['credits_license'] . ': ' . Str::html('a', [
				'href'   => 'https://github.com/dragomano/Light-Portal/blob/master/LICENSE',
				'target' => '_blank',
				'rel'    => 'noopener',
			])->setText('GNU GPLv3');

		return Str::html('a', [
				'href'   => $link,
				'target' => '_blank',
				'rel'    => 'noopener',
				'title'  => LP_VERSION,
			])->setText(LP_NAME) . ' | &copy; ' . Str::html('a', [
				'href' => Config::$scripturl . '?action=credits;sa=light_portal',
			])->setHtml('2019&ndash;' . date('Y')) . ', Bugo | ' . $license;
	}

	private function prepareComponents(): void
	{
		User::$me->isAllowedTo('light_portal_view');

		Utils::$context['portal_translations'] = [
			'Polish'     => ['Adrek', 'jsqx', 'cieplutki'],
			'Spanish'    => ['Rock Lee', 'Diego Andrés'],
			'French'     => ['Papoune57'],
			'Turkish'    => ['gevv', 'Elmacik'],
			'Ukrainian'  => ['valciriya1986'],
			'German'     => ['trucker2006', 'm4z'],
			'Italian'    => ['Darknico'],
			'Portuguese' => ['Costa'],
			'Greek'      => ['Panoulis64'],
			'Dutch'      => ['TeamKC'],
			'Slovenian'  => ['grega'],
		];

		Utils::$context['consultants'] = [
			[
				'name' => 'Sesquipedalian',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=394956',
			],
			[
				'name' => 'Tyrsson',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=155269',
			],
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
				'link' => 'https://code.visualstudio.com'
			],
			[
				'name' => 'Unreal Commander',
				'link' => 'https://x-diesel.com'
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
			[
				'name' => 'PHPStorm',
				'link' => 'https://www.jetbrains.com/phpstorm/'
			],
			[
				'name' => 'Robo',
				'link' => 'https://robo.li/'
			],
			[
				'name' => 'Pest',
				'link' => 'https://pestphp.com'
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
					'name' => 'the MIT License',
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
			],
			[
				'title' => 'Svelte',
				'link' => 'https://github.com/sveltejs/svelte',
				'author' => 'Svelte contributors',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/sveltejs/svelte#MIT-1-ov-file'
				]
			],
			[
				'title' => 'svelte-toggle',
				'link' => 'https://github.com/metonym/svelte-toggle',
				'author' => 'Eric Liu',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/metonym/svelte-toggle/blob/master/LICENSE'
				]
			],
			[
				'title' => 'Svelecte',
				'link' => 'https://github.com/mskocik/svelecte',
				'author' => 'Martin Skočík',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/mskocik/svelecte?tab=MIT-1-ov-file#readme'
				]
			],
		];

		$this->dispatcher->dispatch(PortalHook::credits, ['links' => &$links]);

		shuffle($links);

		Utils::$context['lp_components'] = $links;
	}
}
