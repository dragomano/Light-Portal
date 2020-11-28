<?php

namespace Bugo\LightPortal;

/**
 * Credits.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Credits
{
	/**
	 * The mod credits for action=credits
	 *
	 * Отображаем копирайты на странице action=credits
	 *
	 * @return void
	 */
	public function show()
	{
		global $context, $txt;

		$context['credits_modifications'][] = $this->getCopyrights();

		if (Helpers::request()->filled('sa') && Helpers::request('sa') == 'light_portal') {
			$this->getComponentList();

			loadTemplate('LightPortal/ViewCredits');

			$context['sub_template']   = 'portal_credits';
			$context['robot_no_index'] = true;
			$context['page_title']     = LP_NAME . ' - ' . $txt['lp_used_components'];

			obExit();
		}
	}

	/**
	 * Return copyright information
	 *
	 * Возвращаем информацию об авторских правах
	 *
	 * @return string
	 */
	public function getCopyrights()
	{
		global $scripturl;

		return '<a href="https://dragomano.ru/mods/light-portal" target="_blank" rel="noopener">' . LP_NAME . '</a> | &copy; <a href="' . $scripturl . '?action=credits;sa=light_portal">2019&ndash;2020</a>, Bugo | Licensed under the <a href="https://github.com/dragomano/Light-Portal/blob/master/LICENSE" target="_blank" rel="noopener">GNU GPLv3</a> License';
	}

	/**
	 * Collect information about used components
	 *
	 * Формируем информацию об используемых компонентах
	 *
	 * @return void
	 */
	public function getComponentList()
	{
		global $context;

		isAllowedTo('light_portal_view');

		$links = array(
			array(
				'title' => 'Flexbox Grid',
				'link' => 'https://github.com/evgenyrodionov/flexboxgrid2',
				'author' => 'Kristofer Joseph',
				'license' => array(
					'name' => 'the Apache License',
					'link' => 'https://github.com/evgenyrodionov/flexboxgrid2/blob/master/LICENSE'
				)
			),
			array(
				'title' => 'Font Awesome Free',
				'link' => 'https://fontawesome.com/cheatsheet/free',
				'license' => array(
					'name' => 'the Font Awesome Free License',
					'link' => 'https://github.com/FortAwesome/Font-Awesome/blob/master/LICENSE.txt'
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
				'title' => 'Choices',
				'link' => 'https://github.com/jshjohnson/Choices',
				'author' => 'Josh Johnson',
				'license' => array(
					'name' => 'the MIT License',
					'link' => 'https://github.com/jshjohnson/Choices/blob/master/LICENSE'
				)
			)
		);

		// Adding copyrights of used plugins | Возможность добавить копирайты используемых плагинов
		Subs::runAddons('credits', array(&$links));

		$context['lp_components'] = $links;
	}
}
