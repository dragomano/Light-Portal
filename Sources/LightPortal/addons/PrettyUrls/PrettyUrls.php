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
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PrettyUrls
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public static $addon_type = 'other';

	/**
	 * Give a hint to the PrettyUrls about action=portal
	 *
	 * Подсказываем PrettyUrls про action=portal
	 *
	 * @return void
	 */
	public static function init()
	{
		global $context;

		if (!empty($context['pretty']['action_array'])) {
			if (!in_array('portal', array_values($context['pretty']['action_array'])))
				$context['pretty']['action_array'][] = 'portal';
		}
	}
}
