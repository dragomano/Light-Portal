<?php

declare(strict_types = 1);

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

namespace Bugo\LightPortal;

if (! defined('SMF'))
	die('Hacking attempt...');

final class CreditArea
{
	public function show()
	{
		global $context, $txt;

		$context['credits_modifications'][] = $this->getLink();

		if (! empty($context['current_subaction']) && $context['current_subaction'] === 'light_portal') {
			$this->prepareComponents();

			loadTemplate('LightPortal/ViewCredits');

			$context['sub_template']   = 'portal_credits';
			$context['robot_no_index'] = true;
			$context['page_title']     = LP_NAME . ' - ' . $txt['lp_used_components'];

			obExit();
		}
	}

	public function getLink(): string
	{
		global $user_info, $scripturl, $txt;

		$link = $user_info['language'] === 'russian' ? 'https://dragomano.ru/mods/light-portal' : 'https://custom.simplemachines.org/mods/index.php?mod=4244';

		return '<a href="' . $link . '" target="_blank" rel="noopener" title="' . LP_VERSION . '">' . LP_NAME . '</a> | &copy; <a href="' . $scripturl . '?action=credits;sa=light_portal">2019&ndash;2022</a>, Bugo | ' . $txt['credits_license'] . ': <a href="https://github.com/dragomano/Light-Portal/blob/master/LICENSE" target="_blank" rel="noopener">GNU GPLv3</a>';
	}

	public function prepareComponents()
	{
		global $context;

		isAllowedTo('light_portal_view');

		$context['portal_translations'] = array(
			'Polish'  => array('Adrek', 'jsqx'),
			'Spanish' => array('Rock Lee', 'Diego Andrés'),
			'French'  => array('Papoune57'),
			'Turkish' => array('gevv'),
		);

		$context['testers'] = array(
			array(
				'name' => 'Wylek',
				'link' => 'https://wylek.ru/'
			),
		);

		$context['sponsors'] = array(
			array(
				'name' => 'JetBrains',
				'link' => 'https://www.jetbrains.com/?from=LightPortal'
			),
			array(
				'name' => 'vbgamer45',
				'link' => 'https://www.simplemachines.org/community/index.php?action=profile;u=24876'
			),
			array(
				'name' => 'Crowdin',
				'link' => 'https://crowdin.com/project/light-portal'
			),
		);

		$links = array(
			array(
				'title' => 'Flexbox Grid',
				'link' => 'https://github.com/evgenyrodionov/flexboxgrid2',
				'author' => 'Kristofer Joseph',
				'license' => array(
					'name' => 'the Apache License 2.0',
					'link' => 'https://github.com/evgenyrodionov/flexboxgrid2/blob/master/LICENSE'
				)
			),
			array(
				'title' => 'Font Awesome Free',
				'link' => 'https://fontawesome.com/cheatsheet/free',
				'author' => 'Fonticons, Inc.',
				'license' => array(
					'name' => 'the Font Awesome Free License',
					'link' => 'https://github.com/FortAwesome/Font-Awesome/blob/master/LICENSE.txt'
				)
			),
			array(
				'title' => 'Alpine.js',
				'link' => 'https://github.com/alpinejs/alpine',
				'author' => 'Caleb Porzio and contributors',
				'license' => array(
					'name' => 'the MIT License',
					'link' => 'https://github.com/alpinejs/alpine/blob/master/LICENSE.md'
				)
			),
			array(
				'title' => '@shat/stylenames',
				'link' => 'https://github.com/shatstack/stylenames',
				'author' => 'Kevin Mathmann',
				'license' => array(
					'name' => 'the MIT License',
					'link' => 'https://github.com/shatstack/stylenames/blob/master/LICENSE'
				)
			),
			array(
				'title' => 'Sortable.js',
				'link' => 'https://github.com/SortableJS/Sortable',
				'author' => 'All contributors to Sortable',
				'license' => array(
					'name' => 'the MIT License',
					'link' => 'https://github.com/SortableJS/Sortable/blob/master/LICENSE'
				)
			),
			array(
				'title' => 'Transliteration',
				'link' => 'https://github.com/dzcpy/transliteration',
				'license' => array(
					'name' => 'the MIT License',
					'link' => 'https://github.com/dzcpy/transliteration/blob/master/LICENSE.txt'
				)
			),
			array(
				'title' => 'Slim Select',
				'link' => 'https://slimselectjs.com',
				'author' => 'Brian Voelker',
				'license' => array(
					'name' => 'the MIT License',
					'link' => 'https://github.com/brianvoe/slim-select/blob/master/LICENSE'
				)
			),
			array(
				'title' => 'jscolor Color Picker',
				'link' => 'https://jscolor.com',
				'author' => 'Jan Odvárko – East Desire',
				'license' => array(
					'name' => 'GNU GPL v3',
					'link' => 'https://jscolor.com/download/#open-source-license'
				)
			),
		);

		// Adding copyrights of used plugins
		Addon::run('credits', array(&$links));

		$context['lp_components'] = $links;
	}
}