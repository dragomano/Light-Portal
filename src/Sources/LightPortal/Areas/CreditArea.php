<?php declare(strict_types=1);

/**
 * CreditArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

/**
 * See ?action=credits;sa=light_portal
 */
final class CreditArea
{
	use Helper;

	public function show(): void
	{
		$this->context['credits_modifications'][] = $this->getLink();

		if ($this->context['current_subaction'] && $this->context['current_subaction'] === 'light_portal') {
			$this->prepareComponents();

			$this->context['robot_no_index'] = true;

			$this->context['page_title'] = LP_NAME . ' - ' . $this->txt['lp_used_components'];

			$this->loadTemplate('LightPortal/ViewCredits', 'portal_credits');

			$this->obExit();
		}
	}

	public function getLink(): string
	{
		$link = $this->user_info['language'] === 'russian' ? 'https://dragomano.ru/mods/light-portal' : 'https://custom.simplemachines.org/mods/index.php?mod=4244';

		return '<a href="' . $link . '" target="_blank" rel="noopener" title="' . LP_VERSION . '">' . LP_NAME . '</a> | &copy; <a href="' . $this->scripturl . '?action=credits;sa=light_portal">2019&ndash;' . date('Y') . '</a>, Bugo | ' . $this->txt['credits_license'] . ': <a href="https://github.com/dragomano/Light-Portal/blob/master/LICENSE" target="_blank" rel="noopener">GNU GPLv3</a>';
	}

	public function prepareComponents(): void
	{
		$this->middleware('light_portal_view');

		$this->context['portal_translations'] = [
			'Polish'     => ['Adrek', 'jsqx'],
			'Spanish'    => ['Rock Lee', 'Diego Andrés'],
			'French'     => ['Papoune57'],
			'Turkish'    => ['gevv'],
			'Ukrainian'  => ['valciriya1986'],
			'German'     => ['trucker2006', 'm4z'],
			'Italian'    => ['Darknico'],
			'Portuguese' => ['Costa'],
			'Greek'      => ['Panoulis64'],
			'Czech'      => ['Crowdin Translate'],
			'Danish'     => ['Crowdin Translate'],
			'Dutch'      => ['Crowdin Translate'],
			'Norwegian'  => ['Crowdin Translate'],
			'Swedish'    => ['Crowdin Translate'],
		];

		$this->context['testers'] = [
			[
				'name' => 'Wylek',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=608635'
			],
			[
				'name' => 'Panoulis64',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=301719'
			]
		];

		$this->context['sponsors'] = [
			[
				'name' => 'vbgamer45 <span class="amt">$50</span>',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=24876'
			],
		];

		$this->context['tools'] = [
			[
				'name' => 'Visual Studio Code',
				'link' => 'https://code.visualstudio.com/Download'
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
				'name' => 'Docusaurus',
				'link' => 'https://docusaurus.io'
			]
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
				'title' => 'Latte',
				'link' => 'https://latte.nette.org',
				'author' => 'David Grudl',
				'license' => [
					'name' => 'the New BSD License',
					'link' => 'https://github.com/nette/latte/blob/master/license.md'
				]
			],
			[
				'title' => 'Less.php',
				'link' => 'https://github.com/wikimedia/less.php',
				'author' => 'Wikimedia Foundation',
				'license' => [
					'name' => 'the Apache License 2.0',
					'link' => 'https://github.com/wikimedia/less.php/blob/master/LICENSE'
				]
			],
			[
				'title' => '@shat/stylenames',
				'link' => 'https://github.com/shatstack/stylenames',
				'author' => 'Kevin Mathmann',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/shatstack/stylenames/blob/master/LICENSE'
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
				'title' => 'Tom Select',
				'link' => 'https://tom-select.js.org/',
				'author' => 'Brian Reavis and contributors',
				'license' => [
					'name' => 'the Apache License 2.0',
					'link' => 'https://github.com/orchidjs/tom-select/blob/master/LICENSE'
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
				'title' => 'jscolor Color Picker',
				'link' => 'https://jscolor.com',
				'author' => 'Jan Odvárko – East Desire',
				'license' => [
					'name' => 'the GNU GPL v3',
					'link' => 'https://jscolor.com/download/#open-source-license'
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

		$this->context['lp_components'] = $links;

		shuffle($this->context['lp_components']);
	}
}