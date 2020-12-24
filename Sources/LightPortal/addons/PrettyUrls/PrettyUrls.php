<?php

namespace Bugo\LightPortal\Addons\PrettyUrls;

/**
 * PrettyUrls
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PrettyUrls
{
	/**
	 * @var string
	 */
	public $addon_type = 'other';

	/**
	 * Give a hint to PrettyUrls mod about "action=portal"
	 *
	 * Подсказываем PrettyUrls про "action=portal"
	 *
	 * @return void
	 */
	public function init()
	{
		global $context;

		if (!empty($context['pretty']['action_array']) && !in_array('portal', array_values($context['pretty']['action_array'])))
			$context['pretty']['action_array'][] = 'portal';
	}
}
