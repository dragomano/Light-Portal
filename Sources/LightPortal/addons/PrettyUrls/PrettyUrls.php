<?php

namespace Bugo\LightPortal\Addons\PrettyUrls;

/**
 * PrettyUrls
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
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
	 * @var array
	 */
	public static $addon_type = 'other';

	/**
	 * Run necessary hooks
	 *
	 * Запускаем нужные хуки
	 *
	 * @return void
	 */
	public static function init()
	{
		add_integration_function('integrate_actions', __CLASS__ . '::actions', false, __FILE__);
	}

	/**
	 * Give a hint to the PrettyUrls about action=portal
	 *
	 * Подсказываем PrettyUrls про action=portal
	 *
	 * @return void
	 */
	public static function actions()
	{
		global $context;

		if (!empty($context['pretty']['action_array'])) {
			if (!in_array('portal', array_values($context['pretty']['action_array'])))
				$context['pretty']['action_array'][] = 'portal';
		}
	}
}
