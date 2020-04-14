<?php

namespace Bugo\LightPortal\Addons\Optimus;

/**
 * Optimus
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

class Optimus
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var array
	 */
	public static $addon_type = 'article';

	/**
	 * Select optimus_description column for the frontpage topics
	 *
	 * Выбираем столбец optimus_description при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @return void
	 */
	public static function frontTopics(&$custom_columns)
	{
		global $modSettings;

		if (!class_exists('\Bugo\Optimus\Subs') || empty($modSettings['optimus_allow_change_desc']))
			return;

		$custom_columns[] = 'COALESCE(t.optimus_description, 0) AS optimus_description';
	}

	/**
	 * Change some result data
	 *
	 * Меняем некоторые результаты выборки
	 *
	 * @param array $topics
	 * @param array $row
	 * @return void
	 */
	public static function frontTopicsOutput(&$topics, $row)
	{
		global $modSettings;

		$teaser_size = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;
		if (!empty($teaser_size) && !empty($row['optimus_description']))
			$topics[$row['id_topic']]['preview'] = shorten_subject($row['optimus_description'], $teaser_size - 3);
	}
}
