<?php declare(strict_types=1);

/**
 * CreditArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Helper;

use function isAllowedTo;
use function loadTemplate;
use function obExit;

if (! defined('SMF'))
	die('No direct access...');

final class CreditArea
{
	use Helper;

	public function show()
	{
		$this->context['credits_modifications'][] = $this->getLink();

		if ($this->context['current_subaction'] && $this->context['current_subaction'] === 'light_portal') {
			$this->prepareComponents();

			loadTemplate('LightPortal/ViewCredits');

			$this->context['sub_template']   = 'portal_credits';
			$this->context['robot_no_index'] = true;
			$this->context['page_title']     = LP_NAME . ' - ' . $this->txt['lp_used_components'];

			obExit();
		}
	}

	public function getLink(): string
	{
		$link = $this->user_info['language'] === 'russian' ? 'https://dragomano.ru/mods/light-portal' : 'https://custom.simplemachines.org/mods/index.php?mod=4244';

		return '<a href="' . $link . '" target="_blank" rel="noopener" title="' . LP_VERSION . '">' . LP_NAME . '</a> | &copy; <a href="' . $this->scripturl . '?action=credits;sa=light_portal">2019&ndash;2022</a>, Bugo | ' . $this->txt['credits_license'] . ': <a href="https://github.com/dragomano/Light-Portal/blob/master/LICENSE" target="_blank" rel="noopener">GNU GPLv3</a>';
	}

	public function prepareComponents()
	{
		isAllowedTo('light_portal_view');

		$this->context['portal_translations'] = [
			'Polish'    => ['Adrek', 'jsqx'],
			'Spanish'   => ['Rock Lee', 'Diego Andrés'],
			'French'    => ['Papoune57'],
			'Turkish'   => ['gevv'],
			'Ukrainian' => ['valciriya1986']
		];

		$this->context['testers'] = [
			[
				'name' => 'Wylek',
				'link' => 'https://wylek.ru/'
			],
		];

		$this->context['sponsors'] = [
			[
				'name' => 'JetBrains',
				'link' => 'https://www.jetbrains.com/?from=LightPortal'
			],
			[
				'name' => 'vbgamer45',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=24876'
			],
			[
				'name' => 'Crowdin',
				'link' => 'https://crowdin.com/project/light-portal'
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
				'link' => 'https://fontawesome.com/cheatsheet/free',
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
				'title' => 'Transliteration',
				'link' => 'https://github.com/dzcpy/transliteration',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/dzcpy/transliteration/blob/master/LICENSE.txt'
				]
			],
			[
				'title' => 'Slim Select',
				'link' => 'https://slimselectjs.com',
				'author' => 'Brian Voelker',
				'license' => [
					'name' => 'the MIT License',
					'link' => 'https://github.com/brianvoe/slim-select/blob/master/LICENSE'
				]
			],
			[
				'title' => 'jscolor Color Picker',
				'link' => 'https://jscolor.com',
				'author' => 'Jan Odvárko – East Desire',
				'license' => [
					'name' => 'GNU GPL v3',
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
	}
}
